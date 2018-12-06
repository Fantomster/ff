<?php

namespace api_web\modules\integration\modules\rkeeper\models;

use api\common\models\RkDicconst;
use api\common\models\RkPconst;
use api\common\models\RkService;
use api\common\models\RkWaybill;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\OrderStatus;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class rkeeperService extends WebApi implements ServiceInterface
{
    public static function getServiceId()
    {
        return 1;
    }

    /**
     * Название сервиса
     *
     * @return string
     */
    public function getServiceName()
    {
        return 'R-Keeper';
    }

    /**
     * Информация о лицензии MixCart
     *
     * @return RkService
     */
    public function getLicenseMixCart()
    {
        return RkService::find()->where(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
    }

    /**
     * Статус лицензии сервиса
     *
     * @return bool
     */
    public function getLicenseMixCartActive()
    {
        $license = $this->getLicenseMixCart();
        if ($license) {
            if ($license->status_id == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Настройки
     *
     * @return array
     */
    public function getSettings()
    {
        $query = (new Query())->select(['denom', 'comment', 'type', 'value'])
            ->from(RkDicconst::tableName())
            ->leftJoin(RkPconst::tableName(), RkDicconst::tableName() . '.id = ' . RkPconst::tableName() . '.const_id')
            ->orderBy(RkDicconst::tableName() . '.id')
            ->all(\Yii::$app->db_api);

        $result = [];
        foreach ($query as $row) {
            $r = [
                'name'    => (string)$row['denom'],
                'comment' => (string)$row['comment'],
                'type'    => (int)$row['type'],
            ];
            switch ($row['type']) {
                case 1:
                    $r['value'] = (int)$row['value'];
                    break;
                case 2:
                    $r['value'] = (string)$row['value'];
                    break;
                case 3:
                    $r['value'] = str_pad('', strlen($row['value']), '*');
                    break;
                default:
                    $r['value'] = $row['value'];
            }
            $result[] = $r;
        }
        return $result;
    }

    /**
     * Установка настроек
     *
     * @param $params
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function setSettings($params)
    {
        if (empty($params)) {
            throw new BadRequestHttpException('Empty params settings');
        }
        foreach ($params as $key => $value) {
            if ($model = RkDicconst::findOne(['denom' => $key])) {
                $pmodel = RkPconst::findOne(['const_id' => $model->id, 'org' => $this->user->organization->id]);
                if (empty($pmodel)) {
                    $pmodel = new RkPconst([
                        'const_id' => $model->id,
                        'org'      => $this->user->organization->id
                    ]);
                }
                $pmodel->value = (string)$value;
                if (!$pmodel->validate() || !$pmodel->save()) {
                    throw new ValidationException($pmodel->getFirstErrors());
                }
            } else {
                throw new BadRequestHttpException('Not found ' . $key . ' param!!!');
            }
        }
        return $this->getSettings();
    }

    /**
     * Список опций, отображаемых на главной странице интеграции
     *
     * @return array
     */
    public function getOptions()
    {
        $result = 0;
        $orders = Order::find()->where(['status' => OrderStatus::STATUS_DONE, 'client_id' => $this->user->organization->id])->all();
        if (!empty($orders)) {
            foreach ($orders as $order) {
                if (!RkWaybill::find()->where(['order_id' => $order->id])->exists()) {
                    $result++;
                }
            }
        }
        return [
            'waiting'    => (int)RkWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count(),
            'not_formed' => $result
        ];
    }
}