<?php

namespace console\controllers;

use api\common\models\RabbitQueues;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class for upping consumers from rabbit_queues table
 */
class AbaddonDaemonController extends \console\modules\daemons\components\WatcherDaemonController
{
    /**
     * Список демонов
     *
     * @var array
     */
    public $daemons = [];

    /**
     * Запускать как демон
     *
     * @var bool
     */
    public $demonize = true;

    /**
     * @var int
     */
    protected $sleep = 5;

    /**
     * Реконнекты
     *
     * @return bool
     */
    protected function renewConnections()
    {
        if (isset(\Yii::$app->db_api)) {
            \Yii::$app->db_api->close();
            \Yii::$app->db_api->open();
        }
        if (isset(\Yii::$app->db)) {
            \Yii::$app->db->close();
            \Yii::$app->db->open();
        }
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    protected function getCommandNameBy($className)
    {
        return $className;
    }

    /**
     * get queue name from array of db row
     * @param array $row
     * @return string
     */
    public function getQueueName($row)
    {
        if (!empty($row['organization_id'])) {
            return $row['consumer_class_name'] . '_' . $row['organization_id'];
        }
        return $row['consumer_class_name'];
    }

    /**
     * get full class name with namespace
     * @param string $className shortClassName
     * @return string
     */
    public function getConsumerClassName($className)
    {
        return "console\modules\daemons\classes\\" . $className;
    }

    /**
     * Selecting consumer classes and check queues for count jobs
     * @return array of demons)
     */
    protected function defineJobs()
    {
        sleep($this->sleep);
        $res = \Yii::$app->db_api->createCommand('SELECT * FROM rabbit_queues')->queryAll();

        foreach ($res as $row) {
            try {
                $queue = \Yii::$app->get('rabbit')->setQueue($this->getQueueName($row))->checkQueueCount();
                $kill = $this->checkForKill($row, $queue);
                $this->daemons[$row['consumer_class_name'] . $row['organization_id']] = [
                    'className'     => 'ConsumerDaemonController',
                    'enabled'       => !$kill,
                    'consumerClass' => $row['consumer_class_name'],
                    'orgId'         => $row['organization_id'],
                    'demonize'      => 1,
                    'hardKill'      => $kill,
                ];
            } catch (\Throwable $t) {
                $log = \Yii::getLogger();
                $log->log($t->getMessage(), $log::LEVEL_ERROR, 'abaddon');
            }
        }

//			Testing string
//			$log = \Yii::getLogger();
//			$log->log($kill, $log::LEVEL_ERROR, 'abaddon');

        if (!empty($this->daemons)) {
            foreach ($this->daemons as $daemon) {
                \Yii::$app->controllerMap[$daemon['className']] = ['class' => 'console\modules\daemons\controllers\\' . $daemon['className']];
            }
        }

        return $this->daemons;
    }

    /**
     * Check condition for killing consumer or nor
     * @param array $row sql array from rabbit_queues table row
     * @return boolean
     * */
    protected function checkForKill($row, $queue)
    {
        $consumerClass = $this->getConsumerClassName($row['consumer_class_name']);

        if (!is_null($row['start_executing'])) {
            $startExec = new \DateTime($row['start_executing']);
            $timeOutStartExec = $startExec->getTimestamp() + $consumerClass::$timeoutExecuting;
            if (date('Y-m-d H:i:s', $timeOutStartExec) < date('Y-m-d H:i:s')) {
                (new Query())->createCommand(\Yii::$app->db_api)->update(RabbitQueues::tableName(), [
                    'start_executing' => new Expression('NULL'),
                ], ['id' => $row['id']])->execute();
                return true;
            } else {
                return false;
            }
        }

        if (!is_null($row['last_executed'])) {
            $lastExec = new \DateTime($row['last_executed']);
            $timeOut = $lastExec->getTimestamp() + $consumerClass::$timeout;
            if (date('Y-m-d H:i:s', $timeOut) > date('Y-m-d H:i:s')) {
                return true;
            }
        }

        if ($queue['count'] > 0) {
            return false;
        }

        return true;
    }
}