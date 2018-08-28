<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 18:49
 */

namespace console\modules\daemons\components;

/**
 * Abstract class AbstractConsumer with realization common methods for consumers
 */
abstract class AbstractConsumer
{
    /**@var integer $timeout */
    public static $timeout = 300;
    /**@var string $data data from queue message*/
    public $data;
}