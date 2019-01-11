<?php

namespace api_web\modules\integration\classes\sync;

class TillypadAgent extends ServiceTillypad
{
    public $index = self::DICTIONARY_AGENT;
    public $queueName = 'TillypadAgentSync';
}