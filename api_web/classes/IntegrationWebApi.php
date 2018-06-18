<?php

namespace api_web\classes;

use api\common\models\iiko\iikoWaybill;
use api_web\components\WebApi;
use api_web\modules\integration\interfaces\ServiceInterface;
use yii\base\Exception;

class IntegrationWebApi extends WebApi
{

    private static $service = [
        \api_web\modules\integration\modules\iiko\models\iikoService::class
    ];

    /**
     * Список интеграторов и лицензий
     * @return array
     * @throws Exception
     */
    public function list()
    {
        $result = [];
        foreach (self::$service as $service_class) {
            /**
             * @var $service ServiceInterface
             */
            $service = new $service_class();

            if (!($service instanceof ServiceInterface)) {
                throw new Exception(get_class($service) . ' not implements ServiceInterface');
            }

            $result[] = [
                'service' => $service->getServiceName(),
                'image' => \Yii::$app->params['web'] . 'images/' . $service->getServiceName() . '.jpg',
                'license' => $this->prepareLicense($service->getLicenseMixCart()),
                'options' => $service->getOptions()
            ];
        }
        return ['services' => $result];
    }

    /**
     * Лицензии к выдаче
     * @param $model
     * @return array
     */
    private function prepareLicense($model)
    {
        if (!empty($model)) {
            return [
                "status" => $model->status_id == 2 ? 'Активна' : "Не активна",
                "from" => date('d.m.Y', strtotime($model->fd)),
                "to" => date('d.m.Y', strtotime($model->td)),
                "number" => $model->id
            ];
        }
        return [
            "status" => "Не активна",
            "from" => null,
            "to" => null,
            "number" => null
        ];
    }


    /**
     * iiko: Список Накладных к заказу
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function getOrderWaybillsList(array $post): array
    {
        $orderID = $post['order_id'];
        $iikoWaybill = iikoWaybill::find()->where(['order_id' => $orderID])->andWhere('status_id > 1')->all();
        $arr = [];
        $i = 0;
        foreach ($iikoWaybill as $item){
            $arr[$i]['num_code'] = $item->num_code;
            $arr[$i]['agent_denom'] = $item->agent->denom ?? 'Не указано';
            $arr[$i]['store_denom'] = $item->store->denom ?? 'Не указано';
            $arr[$i]['doc_date'] = \Yii::$app->formatter->format($item->doc_date, 'date');
            $arr[$i]['status_denom'] = $item->status->denom;
            $i++;
        }
        return $arr;
    }
}