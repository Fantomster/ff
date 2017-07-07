<?php

namespace api\modules\v1\modules\mobile\models;

use common\models\User as BaseUser;
use Yii;
use yii\filters\RateLimitInterface;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class User extends BaseUser implements RateLimitInterface
{

    /**
     * @var int
     */
    public $rateWindowSize = 3600;

    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action)
    {
        return [5000, $this->rateWindowSize];
    }

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action)
    {
        $allowance = Yii::$app->cache->get($this->getCacheKey('api_rate_allowance'));
        $timestamp = Yii::$app->cache->get($this->getCacheKey('api_rate_timestamp'));
        return [$allowance, $timestamp];
    }

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @param integer $allowance the number of allowed requests remaining.
     * @param integer $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        Yii::$app->cache->set($this->getCacheKey('api_rate_allowance'), $allowance, $this->rateWindowSize);
        Yii::$app->cache->set($this->getCacheKey('api_rate_timestamp'), $timestamp, $this->rateWindowSize);
    }

    /**
     * @param $key
     * @return array
     */
    public function getCacheKey($key)
    {
        return [__CLASS__, $this->getId(), $key];
    }
    
    /**
     * Send email confirmation to user
     * @param UserToken $userToken
     * @return int
     */
    public function sendEmailConfirmation($userToken)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */

        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;

        // send email
        $user = $this;
        $profile = $user->profile;
        $email = $userToken->data ?: $user->email;
        $subject = Yii::$app->id . " - " . Yii::t("user", "Email Confirmation");
        $result = $mailer->compose('@common/mail/confirmEmail', compact("subject", "user", "profile", "userToken"))
            ->setTo($email)
            ->setSubject($subject)
            ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }
    
    /**
     * Send welcome email after confirmation
     * @param User $client
     * @return int
     */
    public function sendWelcome() {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
		// send email
        $type = $this->organization->type_id;
        $name = $this->profile->full_name;
        $subject = "Добро пожаловать на  f-keeper";
        $result = $mailer->compose('@common/mail/welcome', compact("subject", "type", "name"))
                ->setTo($this->email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization() {
        $organization = new \common\models\Organization();
        return $this->hasOne($organization::className(), ['id' => 'organization_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile() {
        $profile = new \common\models\Profile();
        return $this->hasOne($profile::className(), ['user_id' => 'id']);
    }
}
