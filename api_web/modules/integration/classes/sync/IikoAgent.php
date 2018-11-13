<?php

namespace api_web\modules\integration\classes\sync;

class IikoAgent extends ServiceIiko
{
    public $index = self::DICTIONARY_AGENT;
    public $queueName = 'IikoAgentSync';
}