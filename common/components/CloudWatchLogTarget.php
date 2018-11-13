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
    public $groupName = "";
    public $cloudWatchLog = "";
    
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
            throw $e;//new LogRuntimeException("Error while writing to CloudWatch");
        }
        
    }
}
