<?php

namespace frontend\modules\billing\helpers;


use common\models\Organization;
use common\models\Payment;
use common\models\PaymentTarif;
use common\models\User;

/**
 * Class Tarif
 * @package frontend\modules\billing\helpers
 * @var $user User
 */
class Tarif
{
    public static function getPayment($organization_id = null)
    {
        $user = \Yii::$app->user->identity;
        //Поиск организации
        if (!empty($user) && empty($organization_id)) {
            $organization = $user->organization;
        } else {
            if (!is_null($organization_id)) {
                $organization = Organization::findOne($organization_id);
            }
        }

        if (!empty($organization)) {
            //Определим тип платежа
            //По умолчанию - Подключение
            $type = 2;
            if (Payment::find()->where(['organization_id' => $organization->id, 'type_payment' => $type])->exists()) {
                //Абон плата
                $type = 1;
            }
            //Ищем, может этот ресторан на особых условиях, и у него своя абоненка или подключение
            $model = PaymentTarif::find()
                ->where(['individual' => 1])
                ->andWhere(['organization_id' => $organization->id, 'payment_type_id' => $type])
                ->orderBy('created_at DESC')
                ->one();
            //Если нет, то отдаем ему цены на общих условиях
            //по типу организации
            if (!$model) {
                $model = PaymentTarif::find()
                    ->where(['status' => 1, 'individual' => 0])
                    ->andWhere(['organization_type_id' => $organization->type_id])
                    ->andWhere(['payment_type_id' => $type])
                    ->one();
            }
            return ['price' => $model->price, 'type' => $model->payment_type_id];
        }
        return ['price' => 100000, 'type' => 0];
    }
}