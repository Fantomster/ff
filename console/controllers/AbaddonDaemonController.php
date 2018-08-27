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
    
    
    public function getQueueName($row)
    {
        if (!is_null($row['organization'])) {
            return $row['consumer_class_name'] . '_' . $row['organization'];
        }
        return $row['consumer_class_name'];
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
//				\Yii::$app->get('rabbit')->setQueue($row['consumer_class_name'])->addRabbitQueue('');
            
            $count = \Yii::$app->get('rabbit')->setQueue($this->getQueueName($row))->checkQueueCount();
            
            $this->daemons[$row['consumer_class_name']] = [
                'className'     => 'ConsumerDaemonController',
                'enabled'       => $count > 0 ? true : false,
                'consumerClass' => $row['consumer_class_name'],
                'orgId'         => $row['organization_id'] ?? '',
                'demonize'      => 0,
                'hardKill'      => $count > 0 ? false : true,
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