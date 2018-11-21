<?php

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\components\Registry;
use api_web\modules\integration\classes\documents\Waybill;
use api_web\modules\integration\models\iikoWaybill;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use api_web\helpers\iikoApi;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class ServiceIiko
 *
 * @package api_web\modules\integration\classes\sync
 */
class ServiceIiko extends AbstractSyncFactory
{
    /**
     * @var null
     */
    public $queueName = null;

    /**
     * @var array
     */
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
    ];

    /**
     * @var int
     */
    private $countWaybillSend = 0;

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
                $model = $this->getModel();
                $model->status_id = OrganizationDictionary::STATUS_SEND_REQUEST;
                $model->save();
                return $this->prepareModel($model);
            } else {
                throw new HttpException(402, 'Error send request to RabbitMQ');
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
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
        $records = iikoWaybill::find()
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

        $api = iikoApi::getInstance();
        try {
            $api->auth();
            /** @var iikoWaybill $model */
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
                }
            }
            $api->logout();
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
     * Проверка соединения с iiko
     */
    public function checkConnect()
    {
        $api = iikoApi::getInstance();
        try {
            $api->auth();
            $api->logout();
            return ['result' => true];
        } catch (\Exception $e) {
            $message = $this->prepareErrorMessage($e->getMessage(), $api);
            throw new BadRequestHttpException($message);
        } finally {
            $api->logout();
        }
    }

    /**
     * @param           $res
     * @param   Waybill $model
     * @param           $message
     * @param bool      $success
     * @return mixed
     * @throws BadRequestHttpException
     */
    private function response(&$res, $model, $message, $success = true)
    {
        if ($this->countWaybillSend == 1 and $success === false) {
            throw new BadRequestHttpException($message);
        } else {
            $res[] = $model->prepare();
        }
        return $res;
    }

    /**
     * Получить модель справочника организыйии
     *
     * @return OrganizationDictionary
     */
    private function getModel()
    {
        $dictionary = OuterDictionary::findOne(['service_id' => $this->serviceId, 'name' => $this->index]);
        $model = OrganizationDictionary::findOne([
            'org_id'       => $this->user->organization_id,
            'outer_dic_id' => $dictionary->id
        ]);
        return $model;
    }

    /**
     * Ответ на запрос синхронизации
     *
     * @param $model OrganizationDictionary
     * @return array
     */
    private function prepareModel($model)
    {
        $defaultStatusText = OrganizationDictionary::getStatusTextList()[OrganizationDictionary::STATUS_DISABLED];
        return [
            'id'          => $model->id,
            'name'        => $model->outerDic->name,
            'title'       => \Yii::t('api_web', 'dictionary.' . $model->outerDic->name),
            'count'       => $model->count ?? 0,
            'status_id'   => $model->status_id ?? 0,
            'status_text' => $model->statusText ?? $defaultStatusText,
            'created_at'  => $model->created_at ?? null,
            'updated_at'  => $model->updated_at ?? null
        ];
    }

    /**
     * @param         $message
     * @param IikoApi $api
     * @return string
     */
    private function prepareErrorMessage($message, $api)
    {
        if (strpos($message, '401') !== false) {
            $message = "Ошибка авторизации, проверьте настройки подключения к iiko";
        }
        if (strpos($message, '403') !== false) {
            $message = "Видимо на сервере iiko закончились свободные лицензии." . PHP_EOL;
            $message .= "Лицензий свободно: " . $api->getLicenseCount();
        }
        return $message;
    }
}