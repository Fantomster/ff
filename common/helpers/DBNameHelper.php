<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 31.08.2018
 * Time: 15:47
 */

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
}