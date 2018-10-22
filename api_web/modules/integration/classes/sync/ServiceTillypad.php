<?php

/**
 * Class ServiceTillypad
 *
 * @package   api_web\module\integration\sync
 */

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\components\Registry;
use api_web\helpers\WaybillHelper;
use api_web\modules\integration\models\iikoWaybill;
use common\models\Waybill;
use frontend\modules\clientintegr\modules\tillypad\helpers\TillypadApi;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class ServiceTillypad
 *
 * @package api_web\modules\integration\classes\sync
 */
class ServiceTillypad extends AbstractSyncFactory
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
     * @return array
     * @throws \Exception
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
        if (!isset($request['ids']) && empty($request['ids'])) {
            throw new BadRequestHttpException('empty_param|ids');
        }
        $res = [];
        $records = iikoWaybill::find()
            ->andWhere(['id' => $request['ids'], 'service_id' => $this->serviceId])
            ->andWhere('status_id = :stat', [':stat' => Registry::$waybill_statuses[Registry::WAYBILL_COMPARED]])
            ->all();

        if (!isset($records)) {
            \Yii::error('Cant find waybills for export');
            throw new BadRequestHttpException('Ошибка при экспорте накладных в авторежиме');
        }

        $api = TillypadApi::getInstance();
        if ($api->auth()) {
            /**@var Waybill $model */
            foreach ($records as $model) {
                $transaction = \Yii::$app->db_api->beginTransaction();
                try {
                    $response = $api->sendWaybill($model);
                    if ($response !== true) {
                        \Yii::error('Error during sending waybill');
                        throw new \Exception('Ошибка при отправке. ' . $response);
                    } else {
                        \Yii::error('Waybill' . $model->id . 'has been exported');
                    }

                    $model->status_id = 2;
                    $model->save();
                    $res[$model->id] = true;
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \yii::error('Cant send waybill, rolled back' . $e);
                    $res[$model->id] = [
                        $e->getTraceAsString(),
                        $e->getMessage(),
                    ];
                }
            }
            $api->logout();

        }
        return ['result' => $res];
    }
}