<?php

namespace api_web\components\notice_class;

use Yii;
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

    /**
     * Отправка Email через неделю после регистрации
     * @param $user User
     */
    public function sendEmailWeekend($user)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        Yii::$app->mailer->htmlLayout = '@common/mail/layouts/mail';
        $mailer = Yii::$app->mailer;
        $subject = Yii::t('app', 'common.mail.weekend.subject', ['ru' => 'Вы с нами уже неделю!']);

        if(!empty($user->email)) {
            $mailer->compose('weekend', compact("user"))
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();
        }
    }

    /**
     * Отправка Email через 2 дня после регистрации
     * @param $user User
     */
    public function sendEmailDemonstration($user)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        Yii::$app->mailer->htmlLayout = '@common/mail/layouts/empty';
        $mailer = Yii::$app->mailer;
        $subject = Yii::t('app', 'common.mail.demonstration.subject', ['ru' => 'Как управлять закупками с MixCart']);

        if(!empty($user->email)) {
            $mailer->compose('@common/mail/demonstration')
                ->setFrom(['zahryapina@mixcart.ru' => 'zahryapina@mixcart.ru'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();
        }
    }

    /**
     * Отправка Email через 1 час после логина
     * @param $user User
     */
    public function sendEmailManagerMessage($user)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        Yii::$app->mailer->htmlLayout = '@common/mail/layouts/empty';
        $mailer = Yii::$app->mailer;
        $subject = Yii::t('app', 'common.mail.manager_message.subject', ['ru' => 'Ольга от MixCart']);
        if(!empty($user->email)) {
            $mailer->compose('@common/mail/manager-message')
                ->setFrom(['zahryapina@mixcart.ru' => 'zahryapina@mixcart.ru'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();
        }
    }
}