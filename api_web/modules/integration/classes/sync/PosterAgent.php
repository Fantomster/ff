<?php

namespace api_web\modules\integration\classes\sync;

/**
 * Class PosterAgent
 *
 * @package api_web\modules\integration\classes\sync
 */
class PosterAgent extends ServicePoster
{
    /**
     * @var string
     */
    public $index = self::DICTIONARY_AGENT;
    /**
     * @var string
     */
    public $queueName = 'PosterAgentSync';
}