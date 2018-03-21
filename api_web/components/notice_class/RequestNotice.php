<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 21.03.2018
 * Time: 11:01
 */

namespace api_web\components\notice_class;

use Yii;
use common\models\User;
use common\models\Role;
use common\models\Request;
use common\models\AdditionalEmail;

class RequestNotice
{
    /**
     * Отправляем Email и СМС ресторану, о новой заявке
     * @param Request $request
     * @param User $vendor
     */
    public function addCallback(Request $request, User $vendor)
    {
        #Готовим сообщения
        //Тема Email
        $text = Yii::t('app', 'frontend.controllers.request.mix_two', ['ru' => 'mixcart.ru - заявка №%s']);
        $subject = sprintf($text, $request->id);
        //Сообщение SMS
        $sms_text = Yii::$app->sms->prepareText('sms.request_new_callback', [
            'request_id' => $request->id,
            'vendor_name' => $vendor->organization->name
        ]);
        //Найдем всех сотрудников ресторана, кому должны отправить уведомления
        $clients = User::find()->where([
            'organization_id' => $request->rest_org_id,
            'status' => User::STATUS_ACTIVE,
            'role_id' => Role::ROLE_RESTAURANT_MANAGER
        ])->orWhere(['id' => $request->rest_user_id])->all();
        //Если есть клиенты, а они должн быть :)
        if (!empty($clients)) {
            foreach ($clients as $client) {
                //Отправляем смс ресторану о новом отклике
                if ($client->profile->phone && $client->smsNotification->request_accept == 1) {
                    Yii::$app->sms->send($sms_text, $client->profile->phone);
                }
                //Отправляем емайлы ресторану о новом отклике
                if ($client->email && $client->emailNotification->request_accept == 1) {
                    $mailer = Yii::$app->mailer;
                    $mailer->htmlLayout = 'layouts/request';
                    $mailer->compose('requestNewCallback', compact("request", "client", "vendor"))
                        ->setTo($client->email)
                        ->setSubject($subject)
                        ->send();
                }
            }
        }
        //Теперь найдем дополнительные емайлы в этой организации
        //только те, которые хотят получать эти уведомления
        $additional_email = AdditionalEmail::find()->where([
            'organization_id' => $request->rest_org_id,
            'request_accept' => 1
        ])->all();
        //Если есть такие емайлы, шлем туда
        if (!empty($additional_email)) {
            $client = User::findOne($request->rest_user_id);
            foreach ($additional_email as $add_email) {
                $mailer = Yii::$app->mailer;
                $mailer->htmlLayout = 'layouts/request';
                $mailer->compose('requestNewCallback', compact("request", "client", "vendor"))
                    ->setTo($add_email->email)
                    ->setSubject($subject)
                    ->send();
            }
        }
    }
}