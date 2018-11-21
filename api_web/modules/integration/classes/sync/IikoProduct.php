<?php

namespace api_web\modules\integration\classes\sync;

class IikoProduct extends ServiceIiko
{
    public $index = self::DICTIONARY_PRODUCT;
    public $queueName = 'IikoProductsSync';
}