<?php

namespace console\modules\daemons\controllers;

use api_web\modules\integration\modules\iiko\models\iikoService;
use console\modules\daemons\components\AbstractDaemonController;

use PhpAmqpLib\Message\AMQPMessage;

class IikodaemonController extends AbstractDaemonController
{
    /**
     * @return array|bool
     */
    protected function defineJobs()
    {
        echo "Daemon " . get_class($this) . " job running and working fine." . PHP_EOL;
        $channel = $this->getChannel('log_service_' . iikoService::getServiceId(), 'log');
        while (count($channel->callbacks)) {
            try {
                $channel->wait(null, true, 5);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $timeout) {

            } catch (\PhpAmqpLib\Exception\AMQPRuntimeException $runtime) {
                \Yii::error($runtime->getMessage());
            }
        }
        return false;
    }

    /**
     * @param AMQPMessage $job
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function doJob($job)
    {
        $this->ask($job);
        return true;
    }
}