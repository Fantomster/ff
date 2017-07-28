<?php

namespace api\modules\v1\modules\mobile\models;

use Yii;
use yii\base\Model;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use amnah\yii2\user\models\forms\ForgotForm as BaseForm;

/**
 * Forgot password form
 */
class ForgotForm extends BaseForm
{
    /**
     * Send forgot email
     * @return bool
     */
    public function sendForgotEmail()
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        if ($this->validate()) {

            // get user
            $user = $this->getUser();

            // calculate expireTime
            $expireTime = $this->module->resetExpireTime;
            $expireTime = $expireTime ? gmdate("Y-m-d H:i:s", strtotime($expireTime)) : null;

            $userToken = \common\models\UserToken::generate($user->id, $userToken::TYPE_PASSWORD_RESET, null, $expireTime);

            // modify view path to module views
            $mailer = Yii::$app->mailer;
            $oldViewPath = $mailer->viewPath;
            $mailer->viewPath = $this->module->emailViewPath;

            // send email
            $subject = Yii::$app->id . " - " . Yii::t("user", "Forgot password");
            $result = $mailer->compose('@common/mail/forgotPassword', compact("subject", "user", "userToken"))
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();

            // restore view path and return result
            $mailer->viewPath = $oldViewPath;
            return $result;
        }

        return false;
    }
}