<?php
/**
 * Date: 25.09.2018
 * Time: 12:50
 */

namespace api_web\modules\integration\classes\sync;

class IikoProduct extends ServiceIiko
{
    public $index = self::DICTIONARY_PRODUCT;
    public $queueName = 'IikoProductsSync';
}