<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:32 PM
 */

namespace api_web\classes;

use api\common\models\RabbitQueues;
use api_web\components\WebApi;
use console\modules\daemons\components\AbstractConsumer;
use yii\web\BadRequestHttpException;

/**
 * Class RabbitWebApi
 *
 * @package api_web\classes
 */
class RabbitWebApi extends WebApi
{
    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function addToQueue($request)
    {
        $this->validateRequest($request, ['queue', 'org_id']);

        /**@var AbstractConsumer $queue */
        $queue = $this->getQueueClass($request['queue']);

        if (strpos($request['queue'], 'Merc') === 0) {
            $queue::getUpdateData($request['org_id']);
        } else {
            /** @var RabbitQueues $checkAdd */
            $checkAdd = RabbitQueues::findOne([
                'consumer_class_name' => $request['queue'],
                'organization_id'     => $request['org_id']
            ]);

            if (!is_null($checkAdd)) {
                $lastExec = new \DateTime($checkAdd->last_executed);
                $timeoutLastExec = $lastExec->getTimestamp() + $queue::$timeout;

                if (date('Y-m-d H:i:s', $timeoutLastExec) < date('Y-m-d H:i:s')) {
                    throw new BadRequestHttpException(\Yii::t('api_web', 'dictionaries_were_already_loaded'));
                }
            }

            $queue::getUpdateData($request['org_id']);
        }

        return ['result' => true];
    }

    /**
     * @param $queue
     * @return string
     */
    public function getQueueClass($queue)
    {
        return "console\modules\daemons\classes\\" . $queue;
    }
}