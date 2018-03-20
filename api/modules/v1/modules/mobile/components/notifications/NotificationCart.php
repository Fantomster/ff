<?php
namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationCart {
    
    public static function actionCart($order_id, $is_new = true)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $order = \common\models\Order::findOne(['id' => $order_id]);

        if($order === null)
            return;
        
        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $order->client_id])->all();

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
                    $message->setData(['action' => 'cart',
                            'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.new_order', ['ru'=>"MixCart: новый заказ №"]) . $order->id . "!",
                            'data' => $order->id,
                            'vendor' => $vendor,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'cart',
                            'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.change_order', ['ru'=>'Изменения в заказе №']).$order_id,
                            'data' => $order->id,
                            'vendor' => $vendor,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }

    public static function actionCartDelete($order_id)
    {

        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $order = \common\models\Order::findOne(['id' => $order_id]);

        if($order === null)
            return;

        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $order->client_id])->all();

        $curr_user = Yii::$app->user->getIdentity();

        $vendor = (($order->vendor_id == $curr_user->organization_id) ? 1 : 0);

        foreach ($users as $user)
        {
            $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();
            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));

                $message->setData(['action' => 'cart',
                        'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.new_order', ['ru'=>"MixCart: нзаказ №"]) . $order->id . " удален!",
                        'data' => $order->id,
                        'vendor' => $vendor,
                        'activity' => "Work"]);


                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionCartContent($order_content_id, $is_new = true)
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
                    $message->setData(['action' => 'cartContent',
                            'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.new_position', ['ru'=>'Новая позиция в заказе №']).$order->id,
                            'data' => $orderContent->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'cartContent',
                            'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.change_position', ['ru'=>'Изменения в позиции ']).$orderContent->product_name.Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.order_no', ['ru'=>', заказ №']).$order->id,
                            'data' => $orderContent->id,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionCartContentDelete($orderContent)
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
                $message->setData(['action' => 'cartContentDelete',
                            'title' => Yii::t('app', 'api.modules.v1.modules.mobile.components.notifications.deleted_position', ['ru'=>'Удалена позиция в заказе №']).$order->id,
                            'data' => $orderContent->id,
                            'activity' => "Work"]);

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
}