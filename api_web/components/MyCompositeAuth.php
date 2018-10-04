<?php

namespace api_web\components;

use yii\filters\auth\CompositeAuth;

/**
 * Class MyCompositeAuth
 * @package api_web\components
 */
class MyCompositeAuth extends CompositeAuth
{
    /**
     * @var array
     */
    public $no_auth = [];

    /**
     * Пришлось переопределить, чтобы можно было указывать не только action доступный без авторизации
     * но и целиком путь controller/action
     * @param \yii\base\Action $action
     * @return bool
     */
    protected function isActive($action)
    {
        $is_action_auth = is_array($this->no_auth) ? !in_array(\Yii::$app->request->url, $this->no_auth) : true;

        if ($is_action_auth) {
            $is_action_auth = parent::isActive($action);
        }
        return $is_action_auth;
    }

}