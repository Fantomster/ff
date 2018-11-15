<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 13.12.2017
 * Time: 21:13
 */

namespace frontend\modules\clientintegr\modules\rkws\components;

use common\models\User;


class DebugHelper
{
    private $_logfile = '/runtime/logs/callback_default.log';

    public function setLogFile( $logFile) {

        $this->_logfile = $logFile;
    }

    public function logAppendString($text) {

        //file_put_contents($this->_logfile, $text. PHP_EOL, FILE_APPEND);
        \Yii::info($text, 'rkws_callback_default');
    }
}