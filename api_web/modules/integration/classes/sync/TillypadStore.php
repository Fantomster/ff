<?php

namespace api_web\modules\integration\classes\sync;

class TillypadStore extends ServiceTillypad
{
    public $index = self::DICTIONARY_STORE;
    public $queueName = 'TillypadStoreSync';
}