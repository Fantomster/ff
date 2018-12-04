<?php

namespace common\components\sms\providers;

use common\components\sms\AbstractProvider;

/**
 * Class LocalSmsProvider
 * Чтоб не тратить бабки
 *
 * @package common\components\sms\providers
 */
class LocalSmsProvider extends AbstractProvider
{
    /**
     * Путь до папки с смс
     *
     * @var
     */
    public $path;

    /**
     * @param $message
     * @param $target
     * @return mixed
     */
    public function send($message, $target)
    {
        $path = $this->path . '/' . $target;
        if (!file_exists($path)) {
            mkdir($path, 7777);
        }
        file_put_contents($path . '/sms_' . date('d.m.Y H-i-s') . '.txt', $message);
    }

    /**
     * @param $sms_id
     * @return mixed
     */
    public function checkStatus($sms_id)
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return 1000;
    }
}