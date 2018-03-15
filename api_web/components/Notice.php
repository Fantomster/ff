<?php

namespace api_web\components;

use yii\base\ErrorException;

/**
 * Class Notice
 * @package api_web\components
 */
class Notice
{
    /**
     * Аля фабричный метод
     * @param $class
     * @return mixed
     * @throws ErrorException
     */
    public static function init($class)
    {
        $notice_class = '\api_web\components\notice_class\\' . $class . 'Notice';
        if (!class_exists($notice_class)) {
            throw new ErrorException("Not found {$notice_class}");
        }
        return new $notice_class();
    }
}