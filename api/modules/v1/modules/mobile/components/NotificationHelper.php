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
    
    public static function actionSendMessage($recipient_id, $message_id)
    {
        $users = \common\models\User::find()->where(['organization_id' => $recipient_id])->all();
        $message_data = \common\models\OrderChat::findOne(['id' => $message_id]);
        foreach ($users as $recipient)
        {
            $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => $recipient->id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));
                $message->setData(['action' => 'forgot',
                            'title' => 'Здравствуйте, '.$row->email,
                            'body' => 'Пароль Вашей учетной записи в системе f-keeper изменен.',
                            'action' => 'new_message',
                            'message' => Json::encode($message_data->attributes)]);

                $response = Yii::$app->fcm->send($message);
                //var_dump($response->getStatusCode());
            }
        }
    }
}