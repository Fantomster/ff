<?php

/**
 * Class SyncLog - log data sync
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes;

use yii\web\BadRequestHttpException;

class SyncLog
{

    public static $logIndex;

    public static $logData = [];

    /**
     * Show log in screen or push it to other points
     * @param $data array Data t oshow
     */
    public static function show(array $data = [])
    {
        $prev = 0;
        if (!$data) {
            $data = self::$logData[self::$logIndex];
        }
        echo PHP_EOL . '---------' . PHP_EOL;
        foreach ($data as $k => $v) {
            if (!$prev) {
                $prev = $v['time'];
            }
            if (!isset($init)) {
                $init = $v['time'];
            }
            echo PHP_EOL . ($k + 1) . ') "' . $v['mess'] . '" - [' .
                round(($v['time'] - $prev), 5) . '/' . round(($v['time'] - $init), 5) . '] ms';
            $prev = $v['time'];
        }
    }

    /**
     * Log integration procedure and throw exception if needed
     * @param $message string Log info message
     * @param $exceptionMessage string Exception message
     * @throws BadRequestHttpException
     */
    public static function exit(string $message, string $exceptionMessage = null)
    {
        SyncLog::fix($message);
        # здесь будет логирование неудачных операций синхронизации
        // self::show();
        // exit;
        if ($exceptionMessage) {
            throw new BadRequestHttpException($exceptionMessage);
        }
    }

    /**
     * Log microaction
     * @param $message string Log info message
     */
    public static function fix(string $message)
    {
        if (!self::$logIndex) {
            self::$logIndex = self::uuid4();
        }
        self::$logData[self::$logIndex][] = [
            'time' => (string)microtime(true),
            'mess' => $message,
        ];
    }

    /**
     * Local UUID Generator
     * @return string
     */
    public static function uuid4(): string
    {
        $bytes = function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16);
        $hash = bin2hex($bytes);
        return self::uuidFromHash($hash, 4);
    }

    /**
     * UUID Generator helper
     * @param $hash string
     * @param $version string
     * @return string
     */
    private static function uuidFromHash($hash, $version): string
    {
        return sprintf('%08s-%04s-%04x-%04x-%12s',  // 32 bits for "time_low"
            substr($hash, 0, 8), // 16 bits for "time_mid"
            substr($hash, 8, 4), // 16 bits for "time_hi_and_version", four most significant bits holds version number
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | $version << 12, // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, // two most significant bits holds zero and one for variant DCE1.1
            substr($hash, 20, 12)); // 48 bits for "node"
    }
}