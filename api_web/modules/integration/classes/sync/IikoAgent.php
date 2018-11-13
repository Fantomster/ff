<?php
/**
 * Date: 25.09.2018
 * Time: 12:34
 */

namespace api_web\modules\integration\classes\sync;

class IikoAgent extends ServiceIiko
{
    public $index = self::DICTIONARY_AGENT;
    public $queueName = 'IikoAgentSync';
}