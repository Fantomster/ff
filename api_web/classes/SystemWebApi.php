<?php

namespace api_web\classes;

use api_web\components\WebApi;

/**
 * Class SystemWebApi
 *
 * @package   api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-03
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */
class SystemWebApi extends WebApi
{

    /**
     * Параметры работы сервера со временем
     *
     * @return void
     */
    public function datetime()
    {
        $res = [
            'date_default_timezone_get()' => date_default_timezone_get(),
            'time()'                      => time(),
            'microtime(1)'                => microtime(1),
            'localtime()'                 => localtime(),
            'getdate()'                   => getdate(),
            'gmdate("Y-m-d H:i:s")'       => gmdate("Y-m-d H:i:s"),
            'date("Y-m-d H:i:s")'         => date("Y-m-d H:i:s"),
        ];

        foreach ($res as $v) {
            var_dump($v);
            echo PHP_EOL;
            echo PHP_EOL;
        }
        exit;
    }

}