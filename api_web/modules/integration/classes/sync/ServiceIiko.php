<?php

/**
 * Class ServiceIiko
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\modules\integration\models\iikoWaybill;
use common\models\Waybill;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use yii\web\ServerErrorHttpException;

class ServiceIiko extends AbstractSyncFactory
{
    public $queueName = null;

    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
    ];

    public function sendRequest()
    {
        if (empty($this->queueName)) {
            throw new ServerErrorHttpException('Empty field $queueName in class ' . get_class($this), 500);
        }

        (new RabbitWebApi())->addToQueue([
            "queue"  => $this->queueName,
            "org_id" => $this->user->organization->id
        ]);

        return ['success' => true];
    }

    /**
     * @throws \Exception
     * */
    public function sendWaybill($request)
    {

        $res = true;
        $records = iikoWaybill::find()
            ->andWhere(['id' => $request['ids'], 'service_id' => 2])
            ->andWhere('status_id = :stat', [':stat' => 4])
            ->all();

        if (!isset($records)) {
            \Yii::error('Cant find waybills for export');
            throw new \Exception('Ошибка при экспорте накладных в авторежиме');
        }

        $api = iikoApi::getInstance();

        if ($api->auth()) {
            /**@var Waybill $model */
            foreach ($records as $model) {
                try {
                    $transaction = \Yii::$app->db_api->beginTransaction();

                    $response = $api->sendWaybill($model);
                    if ($response !== true) {
                        \Yii::error('Error during sending waybill');
                        throw new \Exception('Ошибка при отправке. ' . $response);
                    } else {
                        \Yii::error('Waybill' . $model->id . 'has been exported');
                    }

                    $model->bill_status_id = 2;
                    $model->save();
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \yii::error('Cant send waybill, rolled back' . $e);
                    $res = false;
                }
            }
            $api->logout();

        }
        return $res;
    }
}