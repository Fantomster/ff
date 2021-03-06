<?php

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\components\Registry;
use api_web\helpers\TillypadApi;
use api_web\modules\integration\models\TillypadWaybill;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class ServiceTillypad
 *
 * @package api_web\modules\integration\classes\sync
 * @var $queueName           null|string
 * @var $dictionaryAvailable array
 * @var $countWaybillSend    int
 * @var $logCategory         string
 */
class ServiceTillypad extends AbstractSyncFactory
{
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
    ];

    /**
     * @var string
     */
    protected $logCategory = "tillypad_log";

    /**
     * @param array $params
     * @return array
     * @throws ServerErrorHttpException
     */
    public function sendRequest(array $params = []): array
    {
        if (empty($this->queueName)) {
            throw new ServerErrorHttpException('Empty field $queueName in class ' . get_class($this), 500);
        }

        try {
            $sendToRabbit = (new RabbitWebApi())->addToQueue([
                "queue"  => $this->queueName,
                "org_id" => $this->user->organization->id
            ]);

            if ($sendToRabbit) {
                $orgDictionary = $this->getModel();
                $orgDictionary->status_id = OrganizationDictionary::STATUS_SEND_REQUEST;
                $orgDictionary->save();

                if ($orgDictionary->outerDic->name == 'product') {
                    $unitDictionary = OuterDictionary::findOne([
                        'service_id' => $this->serviceId,
                        'name'       => 'unit'
                    ]);
                    $unitModel = OrganizationDictionary::findOne([
                        'org_id'       => $this->user->organization_id,
                        'outer_dic_id' => $unitDictionary->id
                    ]);
                    $unitModel->status_id = OrganizationDictionary::STATUS_SEND_REQUEST;
                    $unitModel->save();
                }

                return $this->prepareModel($orgDictionary);
            } else {
                throw new HttpException(402, 'Error send request to RabbitMQ');
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Метод отправки накладной
     *
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function sendWaybill($request): array
    {
        $this->validateRequest($request, ['ids']);

        $res = [];
        $records = TillypadWaybill::find()
            ->where([
                'id'          => $request['ids'],
                'acquirer_id' => $this->user->organization_id,
                'service_id'  => $this->serviceId
            ])
            ->all();

        if (empty($records)) {
            throw new BadRequestHttpException('waybill_not_found');
        }

        $this->countWaybillSend = count($records);

        $api = TillypadApi::getInstance();
        try {
            if ($api->auth()) {
                /** @var TillypadWaybill $model */
                foreach ($records as $model) {
                    if (empty($model->waybillContents)) {
                        $this->response($res, $model, \Yii::t('api_web', 'service_iiko.empty_waybill_content'));
                    }
                    if (!in_array($model->status_id, [Registry::WAYBILL_COMPARED, Registry::WAYBILL_ERROR])) {
                        if ($model->status_id == Registry::WAYBILL_UNLOADED) {
                            $this->response($res, $model, \Yii::t('api_web', 'service_iiko.already_success_unloading_waybill'));
                        } else {
                            $this->response($res, $model, \Yii::t('api_web', 'service_iiko.no_ready_unloading_waybill'), false);
                        }
                        continue;
                    }
                    /** @var Transaction $transaction */
                    $transaction = \Yii::$app->db_api->beginTransaction();
                    try {
                        $response = $api->sendWaybill($model);
                        if ($response !== true) {
                            $this->response($res, $model, $response, false);
                        }
                        $model->status_id = Registry::WAYBILL_UNLOADED;
                        $model->save();
                        $this->response($res, $model, \Yii::t('api_web', 'service_iiko.success_unloading_waybill'));
                        $transaction->commit();
                        $this->writeInJournal(\Yii::t('api_web', 'integration.waybill_send') . $model->id,
                            Registry::TILLYPAD_SERVICE_ID, $model->acquirer_id);
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        $model->status_id = Registry::WAYBILL_ERROR;
                        $model->save();
                        //Если одна накладная к выгрузке, и произошла ошибка
                        //то в $this->response() будет Exception и мы тупо займем лицензию
                        //Надо отконектиться
                        if ($this->countWaybillSend == 1) {
                            $api->logout();
                        }
                        $this->response($res, $model, $e->getMessage(), false);
                        $this->writeInJournal(\Yii::t('api_web', 'integration.waybill_not_send') . $model->id,
                            Registry::TILLYPAD_SERVICE_ID, $model->acquirer_id);
                    }
                }
                $api->logout();
            }
        } catch (\Exception $e) {
            $api->logout();
            $message = $this->prepareErrorMessage($e->getMessage(), $api);
            throw new BadRequestHttpException($message);
        } finally {
            $api->logout();
        }
        return ['result' => $res];
    }

    /**
     * Проверка коннекта
     *
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function checkConnect($request = [])
    {
        $api = TillypadApi::getInstance($this->user->organization_id);

        if (!empty($request['params'])) {
            if (!empty($request['params']['URL'])) {
                $api->setAttribute('host', $request['params']['URL']);
            }
            if (!empty($request['params']['auth_login'])) {
                $api->setAttribute('login', $request['params']['auth_login']);
            }
            if (!empty($request['params']['auth_password'])) {
                $api->setAttribute('password', $request['params']['auth_password']);
            }
        }

        try {
            if ($api->auth(null, null, 2)) {
                $api->logout();
                return ['result' => true];
            }
        } catch (\Exception $e) {
            $message = $this->prepareErrorMessage($e->getMessage(), $api);
            throw new BadRequestHttpException($message);
        }

        return ['result' => false];
    }

    /**
     * @param             $message
     * @param TillypadApi $api
     * @return string
     */
    private function prepareErrorMessage($message, $api)
    {
        if (strpos($message, 'Код ответа сервера: 0') !== false) {
            $message = "Не удалось соединиться с сервером, проверьте настройки подключения к Tillypad";
        }
        if (strpos($message, '401') !== false) {
            $message = "Ошибка авторизации, проверьте настройки подключения к Tillypad";
        }
        if (strpos($message, '403') !== false) {
            $message = "Видимо на сервере Tillypad закончились свободные лицензии." . PHP_EOL;
            $message .= "Лицензий свободно: " . $api->getLicenseCount();
        }
        return $message;
    }
}
