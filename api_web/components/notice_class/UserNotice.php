<?php

namespace api_web\components\notice_class;

use api_web\models\ForgotForm;
use api_web\models\User;
use yii\web\BadRequestHttpException;

/**
 * Уведомления для пользователя
 * Class UserNotice
 * @package api_web\components\notice_class
 */
class UserNotice
{
    /**
     * Отправляем СМС после регистрации
     * @param $code
     * @param $phone
     * @return mixed
     */
    public function sendSmsCodeToActivate($code, $phone)
    {
        return \Yii::$app->sms->send('Code: ' . $code, $phone);
    }

    /**
     * Отправляем приветственный емайл
     * @param \common\models\User $user
     * @return int
     */
    public function sendEmailWelcome(\common\models\User $user)
    {
        return $user->sendWelcome();
    }

    /**
     * Отправляем письмо с востановлением пароля
     * @param $email
     * @return int
     * @throws BadRequestHttpException
     */
    public function sendEmailRecoveryPassword($email)
    {
        if (empty($email)) {
            throw new BadRequestHttpException('empty Email');
        }

        if (!User::findOne(['email' => $email])) {
            throw new BadRequestHttpException('Пользователь с таким Email не найден в системе.');
        }
        $model = new ForgotForm();
        $model->email = $email;
        return $model->sendForgotEmail();
    }
}