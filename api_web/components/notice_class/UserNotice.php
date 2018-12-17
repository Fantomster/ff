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
     * @param User $user
     * @return int
     */
    public function sendEmailWelcome(User $user)
    {
        return $user->sendWelcome();
    }

    /**
     * Отправляем письмо с востановлением пароля
     * @param string $email
     * @return int
     * @throws BadRequestHttpException
     */
    public function sendEmailRecoveryPassword($email)
    {
        if (empty($email)) {
            throw new BadRequestHttpException('empty Email');
        }

        $user = User::findOne(['email' => $email]);
        if (!$user) {
            throw new BadRequestHttpException('User with this Email was not found in the system.');
        }
        $model = new ForgotForm();
        $model->email = $email;

        $model->newPassword = ForgotForm::generatePassword(8);
        $user->setScenario("reset");
        $user->newPassword = $model->newPassword;
        $user->newPasswordConfirm = $model->newPassword;
        $user->save();
        return $model->sendForgotEmail();
    }

    /**
     * Отправка Email через неделю после регистрации
     * @param User $user
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
     * @param User $user
     */
    public function sendEmailDemonstration($user)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        Yii::$app->mailer->htmlLayout = '@common/mail/layouts/empty';
        $mailer = Yii::$app->mailer;
        $subject = Yii::t('app', 'common.mail.demonstration.subject', ['ru' => 'Как управлять закупками с MixCart']);

        if(!empty($user->email)) {
            $mailer->compose('@common/mail/demonstration', compact("user"))
                ->setFrom(['zahryapina@mixcart.ru' => 'zahryapina@mixcart.ru'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();
        }
    }

    /**
     * Отправка Email через 1 час после логина
     * @param User $user
     */
    public function sendEmailManagerMessage($user)
    {
        /** @var \yii\swiftmailer\Mailer $mailer */
        /** @var \yii\swiftmailer\Message $message */
        Yii::$app->mailer->htmlLayout = '@common/mail/layouts/empty';
        $mailer = Yii::$app->mailer;
        $subject = Yii::t('app', 'common.mail.manager_message.subject', ['ru' => 'Ольга от MixCart']);
        if(!empty($user->email)) {
            //var_dump($user);
            $mailer->compose('@common/mail/manager-message', ['user' => $user])
                ->setFrom(['zahryapina@mixcart.ru' => 'zahryapina@mixcart.ru'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();
        }
    }
}