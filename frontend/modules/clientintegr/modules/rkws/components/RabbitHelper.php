<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 10.07.2018
 * Time: 16:31
 */

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RabbitJournal;
use yii\helpers\Json;
use api\common\models\User;

class RabbitHelper
{
    public function callback($mess)
    {
        if (call_user_func([$this, $mess['action']], $mess['body'])) {
            //$query = "UPDATE rabbit_journal SET success_count = success_count + 1 WHERE id =" . $mess['id'];
            $model = RabbitJournal::find()->where(['id' => $mess['id']])->one();
            $model->success_count = $model->success_count++;
            if (!$model->save) {
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.components.rabbit.journal.not.save', ['ru' => 'Сохранить изменения в журнале Rabbit не удалось.']));
            }
        } else {
            //$query = "UPDATE rabbit_journal SET fail_count = fail_count + 1 WHERE id =" . $mess['id'];
            $model = RabbitJournal::find()->where(['id' => $mess['id']])->one();
            $model->fail_count = $model->fail_count++;
            if (!$model->save) {
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.components.rabbit.journal.not.save', ['ru' => 'Сохранить изменения в журнале Rabbit не удалось.']));
            }
        }

        //\Yii::$app->db_api->createCommand($query)->execute();

        //$sel = "SELECT total_count, success_count, fail_count from rabbit_journal where id = " . $mess['id'];
        $counts = RabbitJournal::find()->where(['id' => $mess['id']])->one();  //Второй раз находим ту же запись?

        //$curr = \Yii::$app->db_api->createCommand($sel)->queryOne();

        $cache = \Yii::$app->cache;
        $clientUsers = $cache->get('clientUsers_' . $mess['id']);

        if (!$clientUsers) {
            //  $clientUsers = (Organization::findOne(['id' => $mess['body']['org_id']]))->users;

            //$sel2 = "SELECT id from user where organization_id = " . $mess['body']['org_id'];

            //$clientUsers = \Yii::$app->db->createCommand($sel2)->queryAll();

            $clientUsers = User::findall()->where(['organization_id' => $mess['body']['org_id']])->column('id');

            if (isset($clientUsers))
                $cache->set('clientUsers_' . $mess['id'], $clientUsers, 60 * 10);
        }

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser['id'];
            \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'isRabbit' => 1,
                    'channel'  => $channel,
                    'action'   => $mess['action'],
                    //'total'    => $curr['total_count'],
                    'total'    => $counts->total_count,
                    //'success'  => $curr['success_count'],
                    'success'  => $counts->success_count,
                    //'failed'   => $curr['fail_count']
                    'failed'   => $counts->fail_count,
                ])
            ]);
        }

        \Yii::$app->db->close();
        \Yii::$app->db_api->close();
    }

    private function fullmap($data)
    {

        //$query = "INSERT into all_map (service_id, supp_id, cat_id, product_id, org_id, koef, is_active)" .
        " values (1, " . $data["supp_id"] . ", " . $data["cat_id"] . ", " . $data["product_id"] . ", " . $data["org_id"] . ",1,1)";

        //\Yii::$app->db_api->createCommand($query)->execute();

        $model = new AllMaps();

        $model->service_id = 1;
        $model->supp_id = $data["supp_id"];
        $model->cat_id = $data["cat_id"];
        $model->product_id = $data["product_id"];
        $model->org_id = $data["org_id"];
        $model->koef = 1;
        $model->is_active = 1;

        if (!$model->save()) {
            //echo "Can't save catalog model";
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.components.all.map.not.insert', ['ru' => 'Добавить новую запись в all_map не удалось.']));
            return false;
        }

        return true;
    }

}