<?php
namespace api\modules\v1\modules\mobile\components;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationHelper {
    
    public static function actionConfirm($user)
    {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->one();
        
        if($fcm == null)
            return;
      
        $message = Yii::$app->fcm->createMessage();
        $message->addRecipient(new Device($fcm->token));
        $message->setData(['action' => 'confirm',
                        'title' => 'Приветствую, '.$user->email,
                        'body' => 
'Меня зовут Шамалов Артур, я являюсь сооснователем сервиса f-keeper.
Благодарю за подтверждение Вашей учетной записи.',
                        'user_token' => $user->access_token]);

        $response = Yii::$app->fcm->send($message);
        //var_dump($response->getStatusCode());
    }
    
    public static function actionForgot($user)
    {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->one();
        
        if($fcm == null)
            return;
      
        $message = Yii::$app->fcm->createMessage();
        $message->addRecipient(new Device($fcm->token));
        $message->setData(['action' => 'forgot',
                        'title' => 'Здравствуйте, '.$user->email,
                        'body' => 
'Пароль Вашей учетной записи в системе f-keeper изменен. '
.'Теперь Вы можете авторизоваться с новым паролем.']);

        $response = Yii::$app->fcm->send($message);
        //var_dump($response->getStatusCode());*/
    }
    
    public static function actionComplete($user)
    {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();
        
        if($fcm == null)
            return;
      
        foreach ($fcm as $row)
        {
            $message = Yii::$app->fcm->createMessage();
            $message->addRecipient(new Device($row->token));
            $message->setData(['action' => 'forgot',
                        'title' => 'Здравствуйте, '.$row->email,
                        'body' => 'Пароль Вашей учетной записи в системе f-keeper изменен.']);

            $response = Yii::$app->fcm->send($message);
            //var_dump($response->getStatusCode());
        }
    }
    
    public static function actionSendMessage($message_id)
    {
        
        $message_data = \common\models\OrderChat::findOne(['id' => $message_id]);
        
        $order = \common\models\Order::findOne(['id' => $message_data->order_id]);
        
        

        $users = \common\models\User::find('organization_id = :client OR organization_id = :vendor', [':client' => $order->client_id, ':vendor' => $order->vendor_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'new_message',
                            'title' => 'Новое сообщение по заказу #'.$message_data->order_id,
                            'body' => $message_data->message,
                            'message' => Json::encode($message_data->attributes),
                            'activity' => "Work"]);

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionRequest($request_id, $is_new = true)
    {
        
        $request = \common\models\Request::findOne(['id' => $request_id]);

        if($request === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $request->rest_org_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'request',
                            'title' => 'Новая заявка №'.$request_id.' '.$request->product,
                            'message' => Json::encode($request->attributes),
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'request',
                            'title' => 'Изменения в заявке №'.$request_id.' '.$request->product,
                            'message' => Json::encode($request->attributes),
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionRequestCallback($requestCallback_id)
    {
        
        $requestC = \common\models\RequestCallback::findOne(['id' => $requestCallback_id]);

        if($requestC === null)
            return;
        
        $request = $requestC->request;

        if($request === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $request->rest_org_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($add)
                {
                    $message->setData(['action' => 'requestCallback',
                            'title' => 'Новый отклик по заявке №'.$request->id.' '.$request->product,
                            'message' => Json::encode($requestC->attributes),
                            'activity' => "Work"]);
                }
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionRelation($rel_id)
    {
        
        $rel = \common\models\RelationSuppRest::findOne(['id' => $rel_id]);

        if($rel === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $rel->rest_org_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));

                    $message->setData(['action' => 'relation',
                            'message' => Json::encode($rel->attributes),
                            'activity' => "Work"]);

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionOrder($order_id, $is_new = true)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $order = \common\models\Order::findOne(['id' => $order_id]);

        if($order === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $order->client_id])->all();
        
        $content = $order->orderContent;

        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'order',
                            'title' => "MixCart: новый заказ №" . $order->id . "!",
                            'data' => $order->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'order',
                            'title' => 'Изменения в заказе №'.$order_id,
                            'data' => $order->id,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionOrderContent($order_content_id, $is_new = true)
    {
        $orderContent = \common\models\OrderContent::findOne(['id' => $order_content_id]);
        
        if($orderContent === null)
            return;
        
        $order = $orderContent->order;

        if($order === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $order->client_id])->all();

        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'orderContent',
                            'title' => 'Новая позиция в заказе №'.$order->id,
                            'message' => $order->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'orderContent',
                            'title' => 'Изменения в позиции '.$orderContent->product_name.', заказ №'.$order->id,
                            'message' => $order->id,
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
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $order->client_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'orderContentDelete',
                            'title' => 'Удалена позиция в заказе №'.$order->id,
                            'message' => $orderContent->id,
                            'activity' => "Work"]);
                }

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
     public static function actionGuide($guide_id, $is_new = true)
    {
        
        $guide = \common\models\guides\Guide::findOne(['id' => $guide_id]);

        if($guide === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $guide->client_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'guide',
                            'title' => 'Новый гайд '.$guide->name,
                            'message' => $guide->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'guide',
                            'title' => 'Изменения в гайде '.$guide->name,
                            'message' => $guide->id,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
    
    public static function actionGuideProduct($guide_product_id, $is_new = true)
    {
        $guideProduct = \common\models\guides\GuideProduct::findOne(['id' => $guide_product_id]);
        
        if($guideProduct === null)
            return;
        
        $guide = $guideProduct->order;

        if($guide === null)
            return;
        
        $users = \common\models\User::find('organization_id = :client', [':client' => $order->client_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                if($is_new)
                {
                    $message->setData(['action' => 'guideProduct',
                            'title' => 'Новая позиция в гайде '.$guide->name,
                            'message' => $guide->id,
                            'activity' => "Work"]);
                }
                else 
                {
                    $message->setData(['action' => 'guideProduct',
                            'title' => 'Изменения в позиции '.$guideProduct->baseProduct->product.', гайд '.$guide->name,
                            'message' => $guide->id,
                            'activity' => "Work"]);
                }
                
                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
}