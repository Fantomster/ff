<?php

namespace api_web\modules\integration\modules\tillypad\models;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\iiko\iikoWaybill;
use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\modules\integration\interfaces\ServiceInterface;
use common\models\Order;
use common\models\OrderStatus;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class TillypadService extends WebApi implements ServiceInterface
{
    /**
     * Название сервиса
     *
     * @return string
     */
    public function getServiceName()
    {
        return 'Tillypad';
    }

    /**
     * id сервиса из таблицы all_service
     *
     * @return string
     */
    public static function getServiceId()
    {
        return Registry::TILLYPAD_SERVICE_ID;
    }

    /**
     * Информация о лицензии MixCart
     *
     * @return \api\common\models\tillypad\TillypadService|array|null|\yii\db\ActiveRecord
     */
    public function getLicenseMixCart()
    {
        return \api\common\models\tillypad\TillypadService::find()->where(['org' => $this->user->organization->id])->orderBy('fd DESC')->one();
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
            ->from(iikoDicconst::tableName())
            ->leftJoin(iikoPconst::tableName(), iikoDicconst::tableName() . '.id = ' . iikoPconst::tableName() . '.const_id')
            ->orderBy(iikoDicconst::tableName() . '.id')
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
            if ($model = iikoDicconst::findOne(['denom' => $key])) {
                $pmodel = iikoPconst::findOne(['const_id' => $model->id, 'org' => $this->user->organization->id]);
                if (empty($pmodel)) {
                    $pmodel = new iikoPconst([
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
                if (!iikoWaybill::find()->where(['order_id' => $order->id])->exists()) {
                    $result++;
                }
            }
        }
        return [
            'waiting'    => (int)iikoWaybill::find()->where(['org' => $this->user->organization->id, 'status_id' => 1])->count(),
            'not_formed' => $result
        ];
    }

    /**
     * Получение главного бизнеса для данной организации
     *
     * @param $org_id
     * @return string
     */
    public static function getMainOrg($org_id)
    {
        $obDicConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        $obConstModel = iikoPconst::findOne(['const_id' => $obDicConstModel->id, 'org' => $org_id]);
        return isset($obConstModel->value) ? $obConstModel->value : $org_id;
    }

    /**
     * Получение дочерних бизнесов для заданной
     *
     * @param $org_id
     * @return array
     */
    public static function getChildOrgsId($org_id)
    {
        $obDicConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        return iikoPconst::find()->select('org')->where(['const_id' => $obDicConstModel->id, 'value' => $org_id])->column();
    }
}