<?php

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\models\iikoWaybill;
use common\models\Waybill;
use api_web\helpers\iikoApi;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
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
            (new RabbitWebApi())->addToQueue([
                "queue"  => $this->queueName,
                "org_id" => $this->user->organization->id
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            return ['success' => false];
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

        if (!isset($records)) {
            \Yii::error('Cant find waybills for export');
            throw new BadRequestHttpException('Ошибка при экспорте накладных в авторежиме');
        }

        $api = iikoApi::getInstance();
        if ($api->auth()) {
            /** @var iikoWaybill $model */
            foreach ($records as $model) {
                if ($model->status_id !== Registry::WAYBILL_COMPARED) {
                    if($model->status_id == Registry::WAYBILL_UNLOADING) {
                        $res[$model->id] = [
                            'success' => true,
                            'message' => \Yii::t('api_web', 'service_iiko.already_success_unloading_waybill'),
                        ];
                    } else {
                        $res[$model->id] = [
                            'success' => false,
                            'message' => \Yii::t('api_web', 'service_iiko.no_ready_unloading_waybill'),
                        ];
                    }
                    continue;
                }
                /** @var Transaction $transaction */
                $transaction = \Yii::$app->db_api->beginTransaction();
                try {
                    $response = $api->sendWaybill($model);
                    if ($response !== true) {
                        \Yii::error('Error during sending waybill');
                        throw new \Exception('Ошибка при отправке. ' . $response);
                    }
                    $model->status_id = Registry::WAYBILL_UNLOADING;
                    $model->save();
                    $res[$model->id] = [
                        'success' => true,
                        'message' => \Yii::t('api_web', 'service_iiko.success_unloading_waybill'),
                    ];
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \yii::error('Cant send waybill, rolled back' . $e);
                    $res[$model->id] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
            $api->logout();
        }
        return ['result' => $res];
    }
}