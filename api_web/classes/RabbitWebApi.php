<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:32 PM
 */

namespace api_web\classes;


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
     */
    public function addToQueue($request)
    {
        if (!empty($request['queue']) && !empty($request['org_id'])) {
            $queue = $this->getQueueClass($request['queue']);
            /**@var AbstractConsumer $queue */
            $queue::getUpdateData($request['org_id']);
        } else {
            throw new BadRequestHttpException('queue or org_id parameters is empty');
        }

        return ['result' => true];
    }

    /**
     * @param $queue
     * @return string
     */
    public function getQueueClass($queue){
        return "console\modules\daemons\classes\\" . $queue;
    }
}