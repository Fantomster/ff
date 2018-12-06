<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:32 PM
 */

namespace api_web\classes;


use api_web\components\WebApi;
use console\modules\daemons\components\IikoSyncConsumer;
use yii\web\BadRequestHttpException;

class RabbitWebApi extends WebApi
{
    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function addToQueue($request)
    {
        if (!empty($request['queue']) && !empty($request['org_id'])) {
            $queue = $this->getQueueClass($request['queue']);
            /**@var $queue IikoSyncConsumer */
            $queue::getUpdateData($request['org_id']);
        } else {
            throw new BadRequestHttpException(\Yii::t('api_web', "queue or org_id parameters is empty", ['ru'=>'queue или org_id parameters указан']));
        }

        return ['result' => true];
    }

    public function getQueueClass($queue){
        return "console\modules\daemons\classes\\" . $queue;
    }
}