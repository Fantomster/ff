<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

//`php yii cron/count`
class CronController extends Controller {

    public function actionCount() {
        $restourants = rand(15, 25);
        $suppliers = rand(5, 10);
        $sql = "update main_counter set supp_count = supp_count + $suppliers, rest_count = rest_count + $restourants ";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionPlusOne() {
        $query = "SELECT supp_count, rest_count, next FROM main_counter LIMIT 1";
        $result = Yii::$app->db->createCommand($query)->queryOne();
        $supp_count = $result["supp_count"];
        $rest_count = $result["rest_count"];
        $next = $result['next'];
        $now = time();
        $nextTime = strtotime($next);
        if (empty($next)) {
            $next = $now;
        }
        if ($nextTime <= $now) {
            //add fake registration
            $orgType = rand(0,1);
            if ($orgType) {
                $rest_count++;
            } else {
                $supp_count++;
            }
            $message = '<i class="fa fa-thumbs-up"></i> Ура! Только что к нам присоединился еще один ' . ($orgType ? "ресторан" : "поставщик");
            $window = rand(1, 15);
            $now = new \DateTime();
            $now->add(new \DateInterval('PT' . $window . 'M'));
            $next = Yii::$app->formatter->asTime($now, "php:Y.m.d H:i:s");
            $query = "UPDATE main_counter SET supp_count=$supp_count, rest_count=$rest_count, next='$next'";
            \Yii::$app->db->createCommand($query)->execute();
            \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => \yii\helpers\Json::encode([
                    'body' => $message,
                    'channel' => 'global',
                ])
            ]);
        }
    }
    
    public function actionTest() {
        Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => \yii\helpers\Json::encode([
                    'body' => '<i class="fa fa-thumbs-up"></i> Ура! К нам присоединился еще один ресторан!',
                    'channel' => 'global',
                ])
            ]);
    }
    
    public function actionSendMail() {
        Yii::$app->mailqueue->process();
    }
}
