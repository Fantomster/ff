<?php
namespace api\modules\v1\modules\mobile\components;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;

class NotificationHelper {
    
    public function actionConfirm($user)
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
    
    public function actionComplete($user)
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
}