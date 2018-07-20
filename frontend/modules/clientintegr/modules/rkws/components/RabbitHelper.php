<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 10.07.2018
 * Time: 16:31
 */

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\AllMaps;
use api\common\models\RabbitJournal;
use common\models\Organization;
use yii\helpers\Json;


class RabbitHelper
{
    public function callback($mess) {

        var_dump($mess);

        if (call_user_func([$this, $mess['action']], $mess['body'])) {
            $query = "UPDATE rabbit_journal SET success_count = success_count + 1 WHERE org_id ='".
                $mess['body']['org_id']."' and action = '".$mess['action'];
        } else {
            $query = "UPDATE rabbit_journal SET fail_count = fail_count + 1 WHERE org_id ='".
                $mess['body']['org_id']."' and action = '".$mess['id'];
        }

        Yii::$app->db_api->createCommand($query)->execute();

        $sel = "SELECT total_count, success_count, fail_count from rabbit_journal where org_id = ".
            $mess['body']['org_id']."' and action = '".$mess['id'];

        $curr =  Yii::$app->db_api->createCommand($sel)->asArray()->queryAll();

        // 'UPDATE account SET forum=:newValue WHERE forum=:oldValue', [':newValue' => 300, ':oldValue' => 200])->execute();

        /* $job = RabbitJournal::find()->andWhere(['org_id' => $mess['body']['org_id'], 'action' => $mess['action']])
            ->andWhere('total_count > (success_count + fail_count)')->one();

        if (!$job) {
            echo "Achtung";
        }

        if (call_user_func([$this, $mess['action']], $mess['body'])) {
            $job->success_count++;
        } else {
            $job->fail_count++;
            $job->fail_content = serialize($mess['body']);
        }

        if (!$job->save()) {
            echo "Jopa kakayato";
        }

        */


        // $clientUsers = (Organization::findOne(['id' => $mess['body']['org_id']]))->users;

        $cache = \Yii::$app->cache;
        $clientUsers = $cache->get('clientUsers_'.$mess['id']);

        if(!$clientUsers) {
           //  $clientUsers = (Organization::findOne(['id' => $mess['body']['org_id']]))->users;

                $sel2 = "SELECT id from user where organization_id = ".$mess['body']['org_id'];

                $clientUsers =  Yii::$app->api->createCommand($sel2)->asArray()->queryAll();


            $cache->set('clientUsers_'.$mess['id'], $clientUsers, 60*10);
        }

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            var_dump($channel);
            \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'isRabbit' => 1,
                    'channel' => $channel,
                    'action' => $mess['action'],
                    'id' => $mess['id'],
                    'total'  => $curr['total_count'],
                    'success' => $curr['success_count'],
                    'failed' => $curr['fail_count']
                ])
            ]);
        }
    }

    private function fullmap($data) {

        $query = "INSERT into all_maps (service_id, supp_id, cat_id, product_id, org_id, koef, is_active)".
        " values (1, ".$data["supp_id"].", ".$data["cat_id"].", ".$data["product_id"].", ".$data["org_id"].",1,1)";

        Yii::$app->db_api->createCommand($query)->execute();

        /*  $model = new AllMaps();

        $model->service_id = 1;
        $model->supp_id = $data["supp_id"];
        $model->cat_id = $data["cat_id"];
        $model->product_id = $data["product_id"];
        $model->org_id = $data["org_id"];
        $model->koef = 1;
        $model->is_active = 1;

        if (!$model->save()) {
            echo "Can't save catalog model";
            return false;
        }
        */
        return true;
    }

}