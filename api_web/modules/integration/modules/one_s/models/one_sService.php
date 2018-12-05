<?php

namespace api_web\modules\integration\modules\one_s\models;

use api\common\models\one_s\one_sPconst;
use api\common\models\one_s\one_sWaybill;
use api\common\models\one_s\OneSDicconst;
use api\common\models\one_s\OneSPconst;
use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\OrderStatus;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class one_sService extends WebApi implements ServiceInterface
{
    /**
     * Название сервиса
     *
     * @return string
     */
    public function getServiceName()
    {
        return 'one_s';
    }

    /**
     * id сервиса из таблицы all_service
     *
     * @return string
     */
    public static function getServiceId()
    {
        return Registry::ONE_S_CLIENT_SERVICE_ID;
    }

    /**
     * Информация о лицензии MixCart
     *
     * @return \api\common\models\one_s\one_sService|array|null|\yii\db\ActiveRecord
     */
    public function getLicenseMixCart()
    {
        return \api\common\models\one_s\OneSService::find()->where(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
    }

    /**
     * Статус лицензии сервиса
     *
     * @return bool
     */
    public function getLicenseMixCartActive()
    {
        $license = $this->getLicenseMixCart();
        if ($license->status_id == 1) {
            return true;
        }
        return false;
    }

    /**
     * Настройки
     */
    public function getSettings()
    {
        $query = (new Query())->select(['denom', 'comment', 'type', 'value'])
            ->from(OneSDicconst::tableName())
            ->leftJoin(OneSPconst::tableName(), OneSDicconst::tableName() . '.id = ' . OneSPconst::tableName() . '.const_id')
            ->orderBy(OneSDicconst::tableName() . '.id')
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
                    $r['value'] = (string)$row['value'];
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
            if ($model = OneSDicconst::findOne(['denom' => $key])) {
                $pmodel = OneSPconst::findOne(['const_id' => $model->id, 'org' => $this->user->organization->id]);
                if (empty($pmodel)) {
                    $pmodel = new OneSPconst([
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
                if (!OneSWaybill::find()->where(['order_id' => $order->id])->exists()) {
                    $result++;
                }
            }
        }
        return [
            'waiting'    => (int)OneSWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count(),
            'not_formed' => $result
        ];
    }
}