<?php

namespace api_web\modules\integration\classes\sync;

/**
 * Class PosterProduct
 *
 * @package api_web\modules\integration\classes\sync
 */
class PosterProduct extends ServicePoster
{
    /**
     * @var string
     */
    public $index = self::DICTIONARY_PRODUCT;
    /**
     * @var string
     */
    public $queueName = 'PosterProductSync';
}