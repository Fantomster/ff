<?php

namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationRequest {

    public static function actionRequest($request) {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;

        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $request->rest_org_id])->all();

        foreach ($users as $user) {
            $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row) {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'request',
                    'title' => '',
                    'data' => Json::encode($request->attributes)]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }

    public static function actionRequestCallback($requestCallback, $is_new) {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;

        $request = $requestCallback->request;

        if ($request === null)
            return;

        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $request->rest_org_id])->all();
        //$vendor = Yii::$app->user->getIdentity();
        $vendor = $requestCallback->user;

        foreach ($users as $user) {
            $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row) {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                    $message->setData(['action' => 'requestCallback',
                        'title' => ($is_new) ? "Новый отклик по Вашей заявке №" . $request->id . " от поставщика " . $vendor->organization->name:"",
                        'data' => Json::encode($requestCallback->attributes),
                        'visible' => ($is_new) ? 1 : 0]);
           
                $response = Yii::$app->fcm->send($message);
            }
        }
    }
    
     public static function actionRequestSetResponsible($request) {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;

        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $request->rest_org_id])->all();

        foreach ($users as $user) {
            $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row) {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'requestSetResponsible',
                    'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.mix_req', ['ru'=>"mixcart.ru - заявка №"]) . $request->id,
                    'message' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.on_your', ['ru'=>"На Вашу заявку №"]).$request->id." ".$request->product.Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.executer_settled', ['ru'=>" назначен исполнитель "]).$request->vendor->name.Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.date', ['ru'=>"
Дата назначения: "]).date('Y-m-d H:i')]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }

}
