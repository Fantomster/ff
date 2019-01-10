<?php

namespace common\helpers;

/**
 * Class DBNameHelper
 *
 * @package common\helpers
 */
class DBNameHelper
{
    /**
     * Имя базы api
     *
     * @return string|null
     */
    public static function getApiName()
    {
        return self::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
    }

    /**
     * Имя базы основной
     *
     * @return string|null
     */
    public static function getMainName()
    {
        return self::getDsnAttribute('dbname', \Yii::$app->db->dsn);
    }

    private static function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return '`' . trim($match[1]) . '`';
        } else {
            return null;
        }
    }
}
