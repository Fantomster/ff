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
        $model = RabbitJournal::find()->where(['id' => $mess['id']])->one();
        if (call_user_func([$this, $mess['action']], $mess['body'])) {
            $attr = 'success_count';
        } else {
            $attr = 'fail_count';
        }

        $model->setAttribute($attr, ($model->{$attr} + 1));

        if (!$model->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.components.rabbit.journal.not.save', ['ru' => 'Сохранить изменения в журнале Rabbit не удалось.']));
        }

        $cache = \Yii::$app->cache;
        $clientUsers = $cache->get('clientUsers_' . $mess['id']);

        if (!$clientUsers) {

            $clientUsers = User::findall()->where(['organization_id' => $mess['body']['org_id']])->column('id');

            if (isset($clientUsers)) {
                $cache->set('clientUsers_' . $mess['id'], $clientUsers, 60 * 10);
            }
        }

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser['id'];
            \Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'isRabbit' => 1,
                    'channel'  => $channel,
                    'action'   => $mess['action'],
                    'total'    => $model->total_count,
                    'success'  => $model->success_count,
                    'failed'   => $model->fail_count,
                ])
            ]);
        }

        \Yii::$app->db->close();
        \Yii::$app->db_api->close();
    }

}