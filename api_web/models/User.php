<?php

namespace api_web\models;

use common\components\mailer\Mailer;
use common\components\mailer\Message;
use Yii;
use common\models\User as BaseUser;
use yii\filters\RateLimitInterface;

/**
 * Class User
 *
 * @package api_web\models
 */
class User extends BaseUser implements RateLimitInterface
{
    /**
     * @var int
     */
    public $rateWindowSize = 3600;

    /**
     * Returns the maximum number of allowed requests and the window size.
     *
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action  the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     *                                  and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action)
    {
        return [5000, $this->rateWindowSize];
    }

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     *
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action  the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     *                                  and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action)
    {
        $allowance = Yii::$app->cache->get($this->getCacheKey('api_rate_allowance'));
        $timestamp = Yii::$app->cache->get($this->getCacheKey('api_rate_timestamp'));
        return [$allowance, $timestamp];
    }

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     *
     * @param \yii\web\Request $request   the current request
     * @param \yii\base\Action $action    the action to be executed
     * @param integer          $allowance the number of allowed requests remaining.
     * @param integer          $timestamp the current timestamp.
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
     * @return mixed
     */
    public function sendWelcome()
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        Yii::$app->mailer->htmlLayout = '@api_web/views/mail/layouts/mail';
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $type = $this->organization->type_id;
        $name = $this->profile->full_name;
        $user = $this;
        $subject = "Добро пожаловать на  MixCart";
        $result = $mailer->compose('@api_web/views/mail/welcome', compact("subject", "type", "name", "user"))
            ->setTo($this->email)
            ->setSubject($subject)
            ->send();

        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        $organization = new \common\models\Organization();
        return $this->hasOne($organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        $profile = new \common\models\Profile();
        return $this->hasOne($profile::className(), ['user_id' => 'id']);
    }
}
