<?php

namespace api_web\modules\integration\classes\sync;

/**
 * Class PosterStore
 *
 * @package api_web\modules\integration\classes\sync
 */
class PosterStore extends ServicePoster
{
    /**
     * @var string
     */
    public $index = self::DICTIONARY_STORE;
    /**
     * @var string
     */
    public $queueName = 'PosterStoreSync';
}