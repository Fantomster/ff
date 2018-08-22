<?php

namespace frontend\helpers;

class GenerationTime
{
    private static $start_time;

    public static function start()
    {
        $start_time = microtime();
        $start_array = explode(" ", $start_time);
        static::$start_time = $start_array[1] + $start_array[0];
    }

    public static function end()
    {
        $end_time = microtime();
        $end_array = explode(" ", $end_time);
        $end_time = $end_array[1] + $end_array[0];
        return $end_time - static::$start_time;
    }
}