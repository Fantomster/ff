<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-14
 * Time: 15:54
 */

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\web\ServerErrorHttpException;

/**
 * Class ServicePoster
 *
 * @package api_web\modules\integration\classes\sync
 */
class ServicePoster extends AbstractSyncFactory
{
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
}
