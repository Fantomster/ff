<?php

namespace console\controllers;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\AbstractConsumer;
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
     * @throws \yii\db\Exception
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
     * @return mixed
     */
    protected function getCommandNameBy($className)
    {
        return $className;
    }

    /**
     * get queue name from array of db row
     *
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
     *
     * @param string $className shortClassName
     * @return string
     */
    public function getConsumerClassName($className)
    {
        return "console\modules\daemons\classes\\" . $className;
    }

    /**
     * Selecting consumer classes and check queues for count jobs
     *
     * @return array of demons)
     */
    protected function defineJobs()
    {
        sleep($this->sleep);
        $res = (new Query())
            ->select('*')
            ->from(RabbitQueues::tableName());
        if (!is_null($this->queuePrefix)) {
            $res->andWhere(['like', 'consumer_class_name', $this->queuePrefix . '%', false]);
        } else {
            $res->andWhere(['not like', 'consumer_class_name', 'Merc%', false]);
        }

        foreach ($res->all(\Yii::$app->db_api) as $row) {
            try {
                $queue = \Yii::$app->get('rabbit')
                    ->setQueue($this->getQueueName($row))
                    ->checkQueueCount();

                $kill = $this->checkForKill($row, $queue);
                $this->daemons[$row['consumer_class_name'] . $row['organization_id'] . $row['store_id']] = [
                    'className'     => 'ConsumerDaemonController',
                    'enabled'       => !$kill,
                    'consumerClass' => $row['consumer_class_name'],
                    'orgId'         => $row['organization_id'],
                    'storeId'       => $row['store_id'],
                    'demonize'      => 1,
                    'hardKill'      => $kill,
                    'lastExec'      => $row['last_executed'],
                ];
            } catch (\Throwable $t) {
                $log = \Yii::getLogger();
                $log->log($t->getMessage(), $log::LEVEL_ERROR);
            }
        }

        if (!empty($this->daemons)) {
            foreach ($this->daemons as $daemon) {
                \Yii::$app->controllerMap[$daemon['className']] = [
                    'class' => 'console\modules\daemons\controllers\\' . $daemon['className']
                ];
            }
        }

        return $this->daemons;
    }

    /**
     * Check condition for killing consumer or nor
     *
     * @param $row
     * @param $queue
     * @return bool
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    protected function checkForKill($row, $queue)
    {
        /** @var AbstractConsumer $consumerClass */
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
