<?php

/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 26.08.2018
 * Time: 18:49
 */

namespace console\modules\daemons\components;

use yii\helpers\BaseStringHelper;

/**
 * Abstract class AbstractConsumer with realization common methods for consumers
 */
abstract class AbstractConsumer
{
    /*     * @var integer $timeout in seconds */

    public static $timeout          = 300;
    /*     * @var string $data data from queue message */
    public $data;
    /*     * @var integer $timeoutExecuting timeout in seconds for execution consumer */
    public static $timeoutExecuting = 600;
    public $logPrefix;

    /**
     * @param $message array|string
     */
    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $message   = $message . PHP_EOL;
        $message   .= str_pad('', 80, '=') . PHP_EOL;
        $className = BaseStringHelper::basename(get_class($this));
        \Yii::info($className . ": ($this->logPrefix) " . $message);
        if (!\Yii::$app->params['disable_daemon_logs']) {
            file_put_contents(\Yii::$app->basePath . "/runtime/daemons/logs/jobs_" . $className . '.log', $message, FILE_APPEND);
        }
    }

}
