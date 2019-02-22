<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-14
 * Time: 15:54
 */

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use api_web\components\Poster;
use api_web\components\Registry;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use api_web\modules\integration\classes\documents\Waybill;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class ServicePoster
 *
 * @package api_web\modules\integration\classes\sync
 */
class ServicePoster extends AbstractSyncFactory
{
    /**
     * @var array
     */
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
    ];

    /**
     * @var string
     */
    protected $logCategory = "poster_log";

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
                /** @var OrganizationDictionary $model */
                $model = $this->getModel();
                $model->status_id = OrganizationDictionary::STATUS_SEND_REQUEST;
                $model->save();
                if ($model->outerDic->name == 'product') {
                    $unitDictionary = OuterDictionary::findOne(['service_id' => $this->serviceId, 'name' => 'unit']);
                    $unitModel = OrganizationDictionary::findOne([
                        'org_id'       => $this->user->organization_id,
                        'outer_dic_id' => $unitDictionary->id
                    ]);
                    $unitModel->status_id = OrganizationDictionary::STATUS_SEND_REQUEST;
                    $unitModel->save();
                }
                return $this->prepareModel($model);
            } else {
                throw new \yii\web\HttpException(402, 'Error send request to RabbitMQ');
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
        $records = Waybill::find()
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

        $api = Poster::getInstance($this->user->organization_id);
        try {
            /** @var Waybill $model */
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
                        Registry::POSTER_SERVICE_ID, $model->acquirer_id);
                } catch (\Throwable $t) {
                    $transaction->rollBack();
                    $model->status_id = Registry::WAYBILL_ERROR;
                    $model->save();
                    $this->response($res, $model, $t->getMessage(), false);
                    $this->writeInJournal(\Yii::t('api_web', 'integration.waybill_not_send') . $model->id,
                        Registry::POSTER_SERVICE_ID, $model->acquirer_id);
                }
            }
        } catch (\Throwable $t) {
            \Yii::error($t->getMessage() . PHP_EOL . $t->getTraceAsString());
            throw new BadRequestHttpException(print_r($t->getMessage()) . PHP_EOL . print_r($t->getTraceAsString()));
        }

        return ['result' => $res];
    }
}
