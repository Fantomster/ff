<?php

namespace console\controllers;
use Yii;
use yii\console\Controller;
//`php yii cron/count`
class CronController extends Controller
{
   public function actionCount() {
        $restourants =  rand(15, 25);
        $suppliers =  rand(5, 10);
        $sql = "update main_counter set supp_count = supp_count + $suppliers, rest_count = rest_count + $restourants ";
        \Yii::$app->db->createCommand($sql)->execute();
    } 
}