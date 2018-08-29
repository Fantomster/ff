<?php

namespace console\controllers;

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
        if (!is_null($row['organization_id'])) {
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
//				Testing string
//            if(!is_null($row['organization_id'])){
//                \Yii::$app->get('rabbit')->setQueue($row['consumer_class_name'] . '_' . $row['organization_id'])->addRabbitQueue('');
//            } else {
//                \Yii::$app->get('rabbit')->setQueue($row['consumer_class_name'])->addRabbitQueue('');
//            }
            
            $count = \Yii::$app->get('rabbit')->setQueue($this->getQueueName($row))->checkQueueCount();
            $consumerClass = $this->getConsumerClassName($row['consumer_class_name']);
            
            if(!is_null($row['last_executed'])){
                $lastExec = new \DateTime($row['last_executed']);
                $timeOut = $lastExec->getTimestamp() + $consumerClass::$timeout;
            }
            
            if (!is_null($row['last_executed']) && date('Y-m-d H:i:s', $timeOut) > date('Y-m-d H:i:s')) {
                $kill = false;
            } elseif ($count > 0) {
                $kill = false;
            } else {
                $kill = true;
            }
            
            $this->daemons[$row['consumer_class_name'] . $row['organization_id']] = [
                'className'     => 'ConsumerDaemonController',
                'enabled'       => !$kill,
                'consumerClass' => $row['consumer_class_name'],
                'orgId'         => $row['organization_id'] ?? '',
                'demonize'      => 0,
                'hardKill'      => $kill,
            ];
        }

//			Testing string
//			$log = \Yii::getLogger();
//			$log->log($this->daemons, $log::LEVEL_ERROR, 'abaddon');
        
        if (!empty($this->daemons)) {
            foreach ($this->daemons as $daemon) {
                \Yii::$app->controllerMap[$daemon['className']] = ['class' => 'console\modules\daemons\controllers\\' . $daemon['className']];
            }
        }
        
        return $this->daemons;
    }
}