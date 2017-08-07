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
    
    public static function actionComplete($user)
    {
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id])->all();
      
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
}