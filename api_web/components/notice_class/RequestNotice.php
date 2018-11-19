<?php

namespace api_web\components\notice_class;

use common\models\RequestCallback;
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
                    $mailer->htmlLayout = '@mail_views/layouts/request';
                    $mailer->compose('@mail_views/requestNewCallback', compact("request", "client", "vendor"))
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
                $mailer->htmlLayout = '@mail_views/layouts/request';
                $mailer->compose('@mail_views/requestNewCallback', compact("request", "client", "vendor"))
                    ->setTo($add_email->email)
                    ->setSubject($subject)
                    ->send();
            }
        }
    }

    /**
     * Отправка уведомлений при установке исполнителя на заявку
     * @param Request $request
     * @param RequestCallback $request_callback
     * @param User $client
     */
    public function setContractor(Request $request, RequestCallback $request_callback, User $client)
    {
        //Для начала подготовим текст уведомлений и шаблоны email
        $sms_text = 'sms.request_set_responsible';
        $subject = Yii::t('app', 'frontend.controllers.request.mix', ['ru' => "mixcart.ru - заявка №%s"]);
        $email_template = 'requestSetResponsibleMailToSupp';
        $client_email_template = 'requestSetResponsible';
        //Данные тексты для рассылки
        $templateMessage = [
            'sms_text' => Yii::$app->sms->prepareText($sms_text, ['request_id' => $request->id]),
            'email_template' => $email_template,
            'email_subject' => sprintf($subject, $request->id),
            'client_email_template' => $client_email_template
        ];
        //Для начала соберем сотрудников постовщика, которым необходимо разослать уведомления
        //Это руководители, и сотрудник который создал отклик
        $vendor_users = User::find()->where([
            'organization_id' => $request_callback->supp_org_id,
            'status' => User::STATUS_ACTIVE,
            'role_id' => Role::ROLE_SUPPLIER_MANAGER
        ])->orWhere(['id' => $request_callback->supp_user_id])->all();

        if (!empty($vendor_users)) {
            //Поехали рассылать
            foreach ($vendor_users as $user) {
                //Отправляем смс поставщику, о принятии решения по его отклику
                if ($user->profile->phone && $user->smsNotification->request_accept == 1) {
                    Yii::$app->sms->send($templateMessage['sms_text'], $user->profile->phone);
                }
                //Отправляем емайлы поставщику, о принятии решения по его отклику
                if ($user->email && $user->emailNotification->request_accept == 1) {
                    $mailer = Yii::$app->mailer;
                    $mailer->htmlLayout = '@mail_views/layouts/request';
                    $mailer->compose('@mail_views/'.$templateMessage['email_template'], [
                        "request" => $request,
                        "vendor" => $user
                    ])->setTo($user->email)
                        ->setSubject($templateMessage['email_subject'])
                        ->send();
                }
            }
        }
        //Так же необходимо отправить емейлы, на доп.адреса
        //только те, которые хотят получать эти уведомления
        $additional_email = AdditionalEmail::find()->where([
            'organization_id' => $request_callback->supp_org_id,
            'request_accept' => 1
        ])->all();
        //Если есть такие емайлы, шлем туда
        if (!empty($additional_email)) {
            $vendor = User::findOne($request_callback->supp_user_id);
            foreach ($additional_email as $add_email) {
                $mailer = Yii::$app->mailer;
                $mailer->htmlLayout = '@mail_views/layouts/request';
                $mailer->compose('@mail_views/'.$templateMessage['email_template'], compact("request", "vendor"))
                    ->setTo($add_email->email)
                    ->setSubject($templateMessage['email_subject'])
                    ->send();
            }
        }
        //Отправим письмо ресторану, что произошло с откликом
        if (!empty($client->email)) {
            $mailer = Yii::$app->mailer;
            $mailer->htmlLayout = '@mail_views/layouts/request';
            $mailer->compose('@mail_views/'.$templateMessage['client_email_template'], compact("request", "client"))
                ->setTo($client->email)
                ->setSubject($templateMessage['email_subject'])
                ->send();
        }
    }
}