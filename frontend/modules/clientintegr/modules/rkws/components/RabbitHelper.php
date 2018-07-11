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

        $job = RabbitJournal::find()->andWhere(['org_id' => $mess['body']['org_id'], 'action' => $mess['action']])
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

        $clientUsers = (Organization::findOne(['id' => $mess['body']['org_id']]))->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            var_dump($channel);
            \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'isRabbit' => 1,
                    'channel' => $channel,
                    'action' => $mess['action'],
                    'total'  => $job->total_count,
                    'success' => $job->success_count,
                    'failed' => $job->fail_count
                ])
            ]);
        }
    }

    private function fullmap($data) {

        $model = new AllMaps();

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

        return true;
    }

}