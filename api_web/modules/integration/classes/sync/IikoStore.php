<?php
/**
 * Date: 25.09.2018
 * Time: 13:06
 */

namespace api_web\modules\integration\classes\sync;

class IikoStore extends ServiceIiko
{
    public $index = self::DICTIONARY_STORE;
    public $queueName = 'IikoStoreSync';
}