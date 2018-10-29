<?php

namespace common\helpers;

class DBNameHelper
{
    public static function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    public static function getApiName()
    {
        return self::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
    }

    public static function getMainName()
    {
        return self::getDsnAttribute('dbname', \Yii::$app->db->dsn);
    }
}