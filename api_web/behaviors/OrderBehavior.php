<?php
/**
 * Date: 02.11.2018
 * Author: Mike N.
 * Time: 12:59
 */

namespace api_web\behaviors;

use api_web\components\Registry;
use api_web\models\User;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
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
        $this->createAndSendWaybill();
        //Если заказ из MC
        if ($this->model->service_id == Registry::MC_BACKEND) {
            //Если заказ перешел в статус "Завершен"
            if ($this->model->status == Order::STATUS_DONE) {
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
        $licenses = License::getAllLicense($this->model->client_id, Registry::$waybill_services, true);
        if (!empty($licenses)) {
            foreach ($licenses as $license) {

                #Получаем настройку [полуавтомат = 2, автомат = 1, выключено = 0]
                $scenario = IntegrationSettingValue::getSettingsByServiceId(
                    $license['service_id'],
                    $this->model->client_id,
                    ['auto_unload_invoice']
                );

                if (!empty($scenario) && $scenario != '0') {
                    //Если полуавтомат, добавляем данные для создания накладных
                    $request = [
                        'order_id'  => $this->model->id,
                        'vendor_id' => $this->model->vendor_id
                    ];
                    //Если полный автомат, добавляем данные для выгрузки
                    if ($scenario == '1') {
                        $request['send'] = true;
                        $request['service_id'] = $license['service_id'];
                    }
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
                        $user = User::findOne(\Yii::$app->user->getIdentity()->getId());
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
                }
            }
        }
        return true;
    }
}