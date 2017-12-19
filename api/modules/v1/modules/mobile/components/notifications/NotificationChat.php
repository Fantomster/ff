<?php
namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationChat {
    
    public static function actionSendMessage($message_id)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1; 
        $message_data = \common\models\OrderChat::findOne(['id' => $message_id]);
        
        $order = \common\models\Order::findOne(['id' => $message_data->order_id]);

        $users = \common\models\User::find()->where('organization_id = :client OR organization_id = :vendor', [':client' => $order->client_id, ':vendor' => $order->vendor_id])->all();
        
        foreach ($users as $user)
        {
         $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();
       

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'new_message',
                            'title' => 'Новое сообщение по заказу №'.$message_data->order_id,
                            'message' => $message_data->message,
                            'data' => Json::encode($message_data->attributes),
                            'activity' => "Work"]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }
}