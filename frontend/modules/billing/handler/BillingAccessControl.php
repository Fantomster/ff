<?php

namespace frontend\modules\billing\handler;

use yii\filters\AccessControl;
use Yii;

class BillingAccessControl extends AccessControl
{
    protected function denyAccess($user)
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            if (Yii::$app->request->isAjax) {
                throw new \Exception(Yii::t('yii', 'У вас нет доступа к этому методу.'));
            } else {
                throw new \Exception(Yii::t('yii', 'Методы доступны только по AJAX'));
            }
        }
    }
}