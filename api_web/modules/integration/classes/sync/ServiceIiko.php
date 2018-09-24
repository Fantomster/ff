<?php

/**
 * Class ServiceIiko
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

class ServiceIiko extends AbstractSyncFactory
{
    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_UNIT,
        self::DICTIONARY_STORE,
    ];
}