<?php

namespace api_web\modules\integration\classes\sync;

class IikoStore extends ServiceIiko
{
    public $index = self::DICTIONARY_STORE;
    public $queueName = 'IikoStoreSync';
}