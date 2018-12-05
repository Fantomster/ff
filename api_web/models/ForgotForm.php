<?php

namespace api_web\models;

use Yii;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use amnah\yii2\user\models\forms\ForgotForm as BaseForm;
use yii\web\BadRequestHttpException;

/**
 * Class ForgotForm
 *
 * @package api_web\models
 */
class ForgotForm extends BaseForm
{
    /**
     * @var string password
     */
    public $newPassword;

    /**
     * @return int
     * @throws BadRequestHttpException
     */
    public function sendForgotEmail()
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        if ($this->validate()) {
            // get user
            $user = $this->getUser();
            $user->newPassword = $this->newPassword;
            // calculate expireTime
            /* $expireTime = $this->module->resetExpireTime;
             $expireTime = $expireTime ? gmdate("Y-m-d H:i:s", strtotime($expireTime)) : null;
             $userToken = UserToken::generate($user->id, UserToken::TYPE_PASSWORD_RESET, null, $expireTime);*/
            // modify view path to module views
            $mailer = Yii::$app->mailer;
            $oldViewPath = $mailer->viewPath;
            $mailer->viewPath = $this->module->emailViewPath;
            // send email
            $subject = Yii::$app->id . " - " . Yii::t("user", "Forgot password");
            $toFrontEnd = true;
            $result = $mailer->compose('@api_web/views/mail/forgotPassword', compact("subject", "user", "userToken", "toFrontEnd"))
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();

            $mailer->viewPath = $oldViewPath;
            return (int)$result;
        } else {
            throw new BadRequestHttpException('User not found');
        }
    }

    /**
     * Get user based on email
     *
     * @return \amnah\yii2\user\models\User|null
     */
    public function getUser()
    {
        if ($this->user === false) {
            $this->user = \common\models\User::findOne(["email" => $this->email]);
        }
        return $this->user;
    }

    /**
     * @param $number int
     * @return string
     */
    public static function generatePassword($number)
    {
        $chars = [];
        $arr = [
            [
                'a', 'b', 'c', 'd', 'e', 'f',
                'g', 'h', 'i', 'j', 'k', 'l',
                'm', 'n', 'o', 'p', 'r', 's',
                't', 'u', 'v', 'x', 'y', 'z'
            ],
            [
                'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N',
                'O', 'P', 'R', 'S', 'T', 'U', 'V',
                'X', 'Y', 'Z'
            ],
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
            ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', ';', ':']
        ];
        //по одному символу с каждого массива символов
        foreach ($arr as $key => $item) {
            $chars[] = $item[rand(0, count($item) - 1)];
        }
        //Добираем случайные символы
        for ($i = 0; count($chars) < $number; $i++) {
            $index = rand(0, count($arr) - 1);
            $chars[] = $arr[$index][rand(0, count($arr[$index]) - 1)];
        }
        //Перемешиваем весь массив
        shuffle($chars);
        return implode('', $chars);
    }
}