<?php
namespace api\modules\v1\modules\mobile\components\notifications;

use Yii;
use common\models\UserFcmToken;
use paragraph1\phpFCM\Recipient\Device;
use yii\helpers\Json;

class NotificationOrganization {
    
    public static function actionRelation($rel_id)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $rel = \common\models\RelationSuppRest::findOne(['id' => $rel_id]);

        if($rel === null)
            return;
        
        $users = \common\models\User::find()->where('organization_id = :client', [':client' => $rel->rest_org_id])->all();
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));

                    $message->setData(['action' => 'relation',
                            'data' => Json::encode($rel->attributes)]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }
    
    public static function actionOrganization($organization)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
       /*$rel = \common\models\RelationSuppRest::findOne(['id' => $rel_id]);

        if($rel === null)
            return;*/
        
        /*$user = Yii::$app->user->getIdentity();
        
        if($user == null)
            return;*/
        
        $users = [];
        
        /*if($user->organization == null)
            return;*/
        
        $users[] = $organization->users;
        
        $orgs = [];
        
        if ($organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
            array_merge ($orgs, $organization->getSuppliers(null));

        if ($organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
        {
            array_merge ($orgs,$organization->getClients());
        }

        $orgs = array_keys($orgs);
        
        $users = array_merge($users, \common\models\User::find()->where('organization id in ('.implode(',', Json::decode($orgs)).')'));
        
        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));

                    $message->setData(['action' => 'organization',
                            'data' => Json::encode($organization->attributes)]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }
    
    public static function actionDelivery($delivery)
    {
        
        $device_id = (Yii::$app->request->headers->get("Device_id") != null) ? Yii::$app->request->headers->get("Device_id") : 1;
        $rel = \common\models\RelationSuppRest::findOne(['id' => $rel_id]);

        if($rel === null)
            return;
        
        //$user = Yii::$app->user->getIdentity();
        $users=[];

        $rels = \common\models\RelationSuppRest::find()->where(['supp_org_id' => $delivery->vendor_id])->all();
        foreach ($els as $rel)
            array_merge ($users,$rel->client->users);

        foreach ($users as $user)
        {
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id <> :device_id', [':user_id' => $user->id, ':device_id' => $device_id])->all();

            foreach ($fcm as $row)
            {
                $message = Yii::$app->fcm->createMessage();
                $message->addRecipient(new Device($row->token));

                    $message->setData(['action' => 'delivery',
                            'data' => Json::encode($delivery->attributes)]);

                $response = Yii::$app->fcm->send($message);
            }
        }
    }
}