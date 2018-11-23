<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components;

use yii\base\InvalidConfigException;

/**
 * Description of CloudWatchLogTarget
 *
 * @author El Babuino
 */
class CloudWatchLogTarget extends \yii\log\Target
{

    public $groupName     = "";
    public $cloudWatchLog = "";
    public $tracesEnabled = true;
    public $brief         = false;

    public function export()
    {
        if (empty($this->groupName)) {
            throw new InvalidConfigException("No groupName found");
        }
        if (empty($this->cloudWatchLog)) {
            throw new InvalidConfigException("No cloudWatchLog component found");
        }
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        try {
            $groupName = preg_replace("/[^a-zA-Z0-9_\/\.\-]/", "", $this->groupName);
            \Yii::$app->get('cloudWatchLog')->writeLog($groupName, date("Y/m/d"), $text);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

//    public function formatMessage($message)
//    {
//        list($text, $level, $category, $timestamp) = $message;
//        $level = \yii\log\Logger::getLevelName($level);
//        if (!is_string($text)) {
//            // exceptions may not be serializable if in the call stack somewhere is a Closure
//            if ($text instanceof \Throwable || $text instanceof \Exception) {
//                $text = (string) $text;
//            } else {
//                $text = \yii\helpers\VarDumper::export($text);
//            }
//        }
//        $traces = [];
//        if ($this->tracesEnabled && isset($message[4])) {
//            foreach ($message[4] as $trace) {
//                $traces[] = "in {$trace['file']}:{$trace['line']}";
//            }
//        }
//
//        $prefix = $this->getMessagePrefix($message);
//        $plc    = $this->brief ? "" : " {$prefix}[$level][$category]";
//        return $this->getTime($timestamp) . "$plc $text" . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
//    }

}
