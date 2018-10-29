<?php
/**
 * Date: 24.10.2018
 * Author: Mike N.
 * Time: 16:32
 */

namespace common\helpers;

class ModelHelper
{
    /**
     * text_text to TextText
     *
     * @param $string
     * @return string
     */
    public static function snake2Camel($string)
    {
        return implode('', array_map(function ($string) {
            return empty($string) ? '_' : ucfirst(strtolower($string));
        }, explode('_', $string)));
    }
}