<?php
namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationUser {
    
    public static function actionConfirm($user)
    {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->one();
        
        if($fcm == null)
            return;
      
        $message = Yii::$app->fcm->createMessage();
        $message->addRecipient(new Device($fcm->token));
        $message->setData(['action' => 'confirm',
                        'title' => 'Приветствую, '.$user->email,
                        'message' => 
'Меня зовут Шамалов Артур, я являюсь сооснователем сервиса MixCart.
Благодарю за подтверждение Вашей учетной записи.',
                        'data' => $user->access_token]);

        $response = Yii::$app->fcm->send($message);
    }
    
    public static function actionForgot($user)
    {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->one();
        
        if($fcm == null)
            return;
      
        $message = Yii::$app->fcm->createMessage();
        $message->addRecipient(new Device($fcm->token));
        $message->setData(['action' => 'forgot',
                        'title' => 'Здравствуйте, '.$user->email,
                        'message' => 
'Пароль Вашей учетной записи в системе MixCart изменен. '
.'Теперь Вы можете авторизоваться с новым паролем.']);

        $response = Yii::$app->fcm->send($message);
    }
    
    public static function actionUpdate($user)
    {
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id = :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->one();
        
        if($fcm == null)
            return;
      
        $message = Yii::$app->fcm->createMessage();
        $message->addRecipient(new Device($fcm->token));
        $message->setData(['action' => 'update_user',
                        'title' => '',
                        'message' => '',
                        'data' => Json::encode($user)]);

        $response = Yii::$app->fcm->send($message);
    }
}