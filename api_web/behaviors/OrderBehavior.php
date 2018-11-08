<?php
/**
 * Date: 02.11.2018
 * Author: Mike N.
 * Time: 12:59
 */

namespace api_web\behaviors;

use api_web\components\Registry;
use api_web\models\User;
use common\models\Order;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use GuzzleHttp\Client;

class OrderBehavior extends Behavior
{
    /** @var \common\models\Order $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * Обновление заказа
     *
     * @param $event
     */
    public function afterUpdate($event)
    {
        //Если заказ из MC и если заказ перешел в статус "Завершен"
        if ($this->model->service_id == Registry::MC_BACKEND && $this->model->status == Order::STATUS_DONE) {
            $this->createAndSendWaybill();
        } elseif (in_array($this->model->service_id, [Registry::VENDOR_DOC_MAIL_SERVICE_ID, Registry::EDI_SERVICE_ID])) {
            if ($this->model->status == Order::STATUS_EDI_ACCEPTANCE_FINISHED){
                $this->createAndSendWaybill();
            }
        }
    }

    /**
     * Создание и отправка накладных по сервисам
     *
     * @return bool
     */
    private function createAndSendWaybill()
    {
        $request = [
            'order_id'  => $this->model->id,
            'vendor_id' => $this->model->vendor_id
        ];

        //Отправка async запроса
        try {
            $client = new Client([
                'base_uri'        => \Yii::$app->params['api_web_url'],
                'timeout'         => 1,
                'handler'         => HandlerStack::create((new CurlMultiHandler())),
                'http_errors'     => false,
                'decode_content'  => false,
                'verify'          => false,
                'cookies'         => false,
                'allow_redirects' => false
            ]);

            //Строим запрос
            $user = User::findOne($this->model->created_by_id);
            $body = [
                "user"    => [
                    "token"    => $user->access_token,
                    "language" => $user->language ?? \Yii::$app->language,
                ],
                "request" => $request
            ];

            $r = new Request("POST", '/waybill/create-and-send-waybill-async', [
                "Content-Type" => "application/json"
            ], \GuzzleHttp\json_encode($body));

            $promise = $client->sendAsync($r);
            $promise->wait(true);
        } catch (\Throwable $e) {
            //Запись в лог, если произошла ошибка отправки
            \Yii::info($e->getMessage(), __METHOD__);
        }
        return true;
    }
}