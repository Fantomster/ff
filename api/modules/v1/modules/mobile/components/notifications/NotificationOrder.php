<?php
namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationOrder {
    
    public static function actionOrder($order_id, $is_new = true)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $order = \common\models\Order::findOne(['id' => $order_id]);

        if($order === null)
            return;
        
        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $order->client_id])->all();

        $content = $order->orderContent;
        
        $curr_user = Yii::$app->user->getIdentity();
        
        $vendor = (($order->vendor_id == $curr_user->organization_id) ? 1 : 0);
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();
            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'order',
                            'title' => "MixCart: новый заказ №" . $order->id . "!",
                            'data' => $order->id,
                            'vendor' => $vendor,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'order',
                            'title' => 'Изменения в заказе №'.$order_id,
                            'data' => $order->id,
                            'vendor' => $vendor,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionOrderContent($order_content_id, $is_new = true)
    {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $orderContent = \common\models\OrderContent::findOne(['id' => $order_content_id]);
        
        if($orderContent === null)
            return;
        
        $order = $orderContent->order;

        if($order === null)
            return;
        
        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $order->client_id])->all();

        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'orderContent',
                            'title' => 'Новая позиция в заказе №'.$order->id,
                            'data' => $order->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'orderContent',
                            'title' => 'Изменения в позиции '.$orderContent->product_name.', заказ №'.$order->id,
                            'data' => $order->id,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionOrderContentDelete($orderContent)
    {
        if($orderContent === null)
            return;
        
        $order = $orderContent->order;

        if($order === null)
            return;
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $order->client_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'orderContentDelete',
                            'title' => 'Удалена позиция в заказе №'.$order->id,
                            'data' => $orderContent->id,
                            'activity' => "Work"]);

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
}