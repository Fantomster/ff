<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;
use common\models\AdditionalEmail;
use common\models\ManagerAssociate;
use common\models\User;
use common\components\AccessRule;
use common\models\Organization;
use common\models\Request;
use common\models\Role;
use common\models\RequestCallback;
use common\models\RequestCounters;

/**
 * Управление заявками
 */
class RequestController extends DefaultController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'list',
                            'view',
                        ],
                        'allow'   => true,
                        // Allow restaurant managers
                        'roles'   => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_ONE_S_INTEGRATION,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                    [
                        'actions' => [
                            'close-request',
                            'save-request',
                            'set-responsible',
                            'add-supplier',
                        ],
                        'allow'   => true,
                        // Allow restaurant managers
                        'roles'   => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                    [
                        'actions' => [
                            'add-callback',
                        ],
                        'allow'   => true,
                        // Allow restaurant managers
                        'roles'   => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Создание заявки
     * @return array|bool
     */
    public function actionSaveRequest()
    {
        $currentUser = $this->currentUser;

        if ($currentUser->organization->type_id != Organization::TYPE_RESTAURANT) {
            return false;
        }

        $organization          = $currentUser->organization;
        $request               = new Request();
        $request->rest_org_id  = $currentUser->organization_id;
        $request->rest_user_id = $currentUser->id;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $request->load(Yii::$app->request->post());
            $organization->load(Yii::$app->request->post());

            $validForm = ActiveForm::validate($request);

            if (empty($organization->lat) ||
                    empty($organization->lng) ||
                    empty($organization->place_id) ||
                    empty($organization->country)) {
                return ['organization-address' => false];
            }

            if ($validForm) {
                return $validForm;
            } else {
                if (Yii::$app->request->post('step') == 3) {
                    if ($request->validate() && $organization->validate()) {
                        $organization->city = $organization->locality;
                        $organization->save();
                        $request->save();
                        return ['saved' => true];
                    } else {
                        return ['error' => ['organization' => $organization->errors, 'request' => $request->errors]];
                    }
                } else {
                    return $validForm;
                }
            }
        }
    }

    /**
     * Список заявок
     * @return string
     */
    public function actionList()
    {
        $organization = $this->currentUser->organization;
        $profile      = $this->currentUser->profile;
        $search       = ['like', 'product', \Yii::$app->request->get('search') ?: ''];
        $category     = \Yii::$app->request->get('category') ? ['category' => \Yii::$app->request->get('category')] : [];

        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $dataListRequest = new ActiveDataProvider([
                'query'      => Request::find()->where(['rest_org_id' => $organization->id])->andWhere($search)->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 15,
                ],
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial("list-client", compact('dataListRequest', 'organization', 'profile'));
            } else {
                return $this->render("list-client", compact('dataListRequest', 'organization', 'profile'));
            }
        }
        if ($organization->type_id == Organization::TYPE_SUPPLIER) {
            if (\common\models\DeliveryRegions::find()->where(['supplier_id' => $organization->id, 'exception' => 0])->exists()) {

                $my   = \Yii::$app->request->get('myOnly') == 2 ? ['responsible_supp_org_id' => $organization->id] : [];
                $rush = \Yii::$app->request->get('rush') == 2 ? ['rush_order' => 1] : [];

                $query = Request::find()
                        ->joinWith('client')
                        ->orderBy('id DESC');

                //Массив в достывками
                $deliveryRegions = $organization->deliveryRegionAsArray;
                //Доступные для доставки регионы
                if (!empty($deliveryRegions['allow'])) {
                    foreach ($deliveryRegions['allow'] as $row) {
                        if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                            $p = $row['administrative_area_level_1'] . $row['locality'];
                            $query->orWhere('CONCAT(administrative_area_level_1, locality) = :p', [':p' => $p]);
                        } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                            $query->orWhere(['=', 'locality', $row['locality']]);
                        } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                            $query->orWhere(['=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                        }
                    }
                }
                //Условия для исключения доставки с регионов
                if (!empty($deliveryRegions['exclude'])) {
                    if (!empty($deliveryRegions['exclude'])) {
                        foreach ($deliveryRegions['exclude'] as $row) {
                            if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                                $p = $row['administrative_area_level_1'] . $row['locality'];
                                $query->andWhere('CONCAT(administrative_area_level_1, locality) <> :s', [':s' => $p]);
                            } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                                $query->andWhere(['!=', 'locality', $row['locality']]);
                            } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                                $query->andWhere(['!=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                            }
                        }
                    }
                }

                $query->andWhere(['>=', 'end', new \yii\db\Expression('NOW()')])
                        ->andWhere(['active_status' => Request::ACTIVE])
                        ->andWhere($search)
                        ->andWhere($category)
                        ->andWhere($my)
                        ->andWhere($rush);

                $dataListRequest = new ActiveDataProvider([
                    'query'      => $query,
                    'pagination' => [
                        'pageSize' => 15,
                    ],
                ]);

                if (Yii::$app->request->isPjax) {
                    return $this->renderPartial("list-vendor", compact('dataListRequest', 'organization'));
                } else {
                    return $this->render("list-vendor", compact('dataListRequest', 'organization'));
                }
            } else {
                return $this->render("delivery-vendor");
            }
        }
    }

    /**
     * Просмотр заявки
     * @param $id
     * @return string|Response
     */
    public function actionView($id)
    {
        $user    = $this->currentUser;
        $query   = null;
        $view    = 'redirect';
        //Заявка
        $request = Request::find()->where(['id' => $id])->one();
        //Если нет такой заявки отправляем в список
        if (empty($request)) {
            return $this->redirect("list");
        }
        //автор - организация, ресторан если быть точнее
        $author = Organization::findOne(['id' => $request->rest_org_id]);

        if ($user->organization->type_id == Organization::TYPE_RESTAURANT) {
            //Вьюха
            $view          = 'client';
            //Количество комментариев
            $countComments = RequestCallback::find()->where(['request_id' => $id])->count();
            //Строка запроса
            $query         = RequestCallback::find()->where([
                        'request_id' => $id
                    ])->orderBy('id DESC');
        }

        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            //Вьюха
            $view              = 'vendor';
            //Строка запроса
            $query             = RequestCallback::find()->where([
                        'request_id'  => $id,
                        'supp_org_id' => $user->organization_id
                    ])->orderBy('id DESC');
            //Оставил или нет предложение
            $trueFalseCallback = RequestCallback::find()->where([
                        'request_id'  => $id,
                        'supp_org_id' => $user->organization_id
                    ])->exists();
            //Просмотр этой заявки
            RequestCounters::hit($id, $user->id);
        }

        if ($view == 'redirect' or empty($query)) {
            return $this->redirect("list");
        }

        $dataCallback = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);

        return $this->render("view-" . $view, compact('request', 'countComments', 'author', 'dataCallback', 'trueFalseCallback'));
    }

    /**
     * Устанавливаем или снимаем исполнителя заявки
     * @return array
     */
    public function actionSetResponsible()
    {
        //Формат ответа
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaction                = Yii::$app->db->beginTransaction();
        try {
            $client = $this->currentUser;
            if (Yii::$app->request->isAjax) {
                //Получаем параметры
                $id             = Yii::$app->request->post('id');
                $responsible_id = Yii::$app->request->post('responsible_id');
                //Проверим существование заявки
                if (!Request::find()->where(['rest_org_id' => $client->organization_id, 'id' => $id])->exists()) {
                    throw new \Exception('Не существует такой Request::id = ' . $id);
                }
                //Проверим реально ли есть ответ на нее, от этой организации
                $request_callback = RequestCallback::find()->where([
                            'request_id'  => $id,
                            'supp_org_id' => $responsible_id
                        ])->one();
                if (empty($request_callback)) {
                    throw new \Exception('Не существует такой RequestCallback::request_id = ' . $id);
                }
                //Получим модель заявки
                $request = Request::find()->where(['id' => $id])->with('vendor')->one();

                //Если исполнитель уже эта организация, значит запрос на ее снятие
                //мы должны ее снять, и отправить уведомления что сняли
                $reject = false;
                if ($request->responsible_supp_org_id == $responsible_id) {
                    $reject                           = true;
                    $request->responsible_supp_org_id = null;
                } else {
                    //Если попали сюда, значит установили исполнителя
                    $request->responsible_supp_org_id = $responsible_id;
                    //Сделано так, для того чтобы во вьюхе по связям можно было определить кого назначили
                    $request->save();
                    $request->refresh();
                }

                //Тут пошли уведомления
                //Для начала подготовим текст уведомлений и шаблоны email
                $sms_text              = 'sms.request_set_responsible';
                $subject               = Yii::t('app', 'frontend.controllers.request.mix', ['ru' => "mixcart.ru - заявка №%s"]);
                $email_template        = 'requestSetResponsibleMailToSupp';
                $client_email_template = 'requestSetResponsible';
                //Если $reject значит сняли с заявки
                if ($reject) {
                    $sms_text              = 'sms.request_unset_responsible';
                    $email_template        = 'requestSetResponsibleMailToSuppReject';
                    $client_email_template = 'requestSetResponsibleReject';
                }
                //Данные тексты для рассылки
                $templateMessage = [
                    'sms_text'              => Yii::$app->sms->prepareText($sms_text, ['request_id' => $request->id]),
                    'email_template'        => $email_template,
                    'email_subject'         => sprintf($subject, $request->id),
                    'client_email_template' => $client_email_template
                ];
                //Для начала соберем сотрудников постовщика, которым необходимо разослать уведомления
                //Это руководители, и сотрудник который создал отклик
                $vendor_users    = $request_callback->recipientsListForVendor;

                if (!empty($vendor_users)) {
                    //Поехали рассылать
                    foreach ($vendor_users as $user) {
                        //Отправляем смс поставщику, о принятии решения по его отклику
                        if ($user->profile->phone && $user->smsNotification->request_accept == 1) {
                            Yii::$app->sms->send($templateMessage['sms_text'], $user->profile->phone);
                        }
                        //Отправляем емайлы поставщику, о принятии решения по его отклику
                        if ($user->email && $user->emailNotification->request_accept == 1) {
                            $mailer             = Yii::$app->mailer;
                            $mailer->htmlLayout = 'layouts/request';
                            $mailer->compose($templateMessage['email_template'], [
                                        "request" => $request,
                                        "vendor"  => $user
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
                            'request_accept'  => 1
                        ])->all();
                //Если есть такие емайлы, шлем туда
                if (!empty($additional_email)) {
                    $vendor = User::findOne($request_callback->supp_user_id);
                    foreach ($additional_email as $add_email) {
                        $mailer             = Yii::$app->mailer;
                        $mailer->htmlLayout = 'layouts/request';
                        $mailer->compose($templateMessage['email_template'], compact("request", "vendor"))
                                ->setTo($add_email->email)
                                ->setSubject($templateMessage['email_subject'])
                                ->send();
                    }
                }
                //Отправим письмо ресторану, что произошло с откликом
                if (!empty($client->email)) {
                    $mailer             = Yii::$app->mailer;
                    $mailer->htmlLayout = 'layouts/request';
                    $mailer->compose($templateMessage['client_email_template'], compact("request", "client"))
                            ->setTo($client->email)
                            ->setSubject($templateMessage['email_subject'])
                            ->send();
                }

                //Вносим изменения в базу
                if ($request->save()) {
                    $transaction->commit();
                    return ['success' => true];
                } else {
                    throw new \Exception('Не удалось сохранить модель $request');
                }
            } else {
                throw new \Exception('Только AJAX запросы');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'error' => $e->getCode()];
        }
    }

    /**
     * @return array
     */
    public function actionAddSupplier()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $client                     = $this->currentUser;
            $request_id                 = Yii::$app->request->post('request_id');
            $vendor                     = Organization::findOne(['id' => Yii::$app->request->post('supp_org_id')]);
            $callback                   = RequestCallback::find()->where(['supp_org_id' => $vendor->id, 'request_id' => $request_id])->one();

            if (isset($callback)) {

                $relationSuppRest = \common\models\RelationSuppRest::find()->where([
                            'rest_org_id' => $client->organization_id,
                            'supp_org_id' => $vendor->id
                        ])->one();

                if (empty($relationSuppRest)) {
                    $relationSuppRest = new \common\models\RelationSuppRest();
                }

                $relationSuppRest->deleted     = false;
                $relationSuppRest->rest_org_id = $client->organization_id;
                $relationSuppRest->supp_org_id = $vendor->id;
                $relationSuppRest->invite      = \common\models\RelationSuppRest::INVITE_OFF;

                if ($relationSuppRest->save()) {
                    $rows = User::find()->where(['organization_id' => $vendor->id, 'role_id' => Role::ROLE_SUPPLIER_MANAGER])->all();
                    foreach ($rows as $row) {
                        $managerAssociate = ManagerAssociate::findOne(['manager_id' => $row->id, 'organization_id' => $client->organization_id]);
                        if (!$managerAssociate) {
                            $managerAssociate                  = new ManagerAssociate();
                            $managerAssociate->manager_id      = $row->id;
                            $managerAssociate->organization_id = $client->organization_id;
                            $managerAssociate->save();
                        }
                    }
                    $request     = Request::findOne(['id' => $request_id]);
                    $vendorUsers = $callback->recipientsListForVendor;
                    if ($client->email) {
                        $mailer             = Yii::$app->mailer;
                        $subject            = Yii::t('message', 'frontend.controllers.request.request_two', ['ru' => "mixcart.ru - заявка №"]) . $request->id;
                        $mailer->htmlLayout = 'layouts/request';
                        $mailer->compose('requestInviteSupplierMailToRest', compact("request", "client"))
                                ->setTo($client->email)
                                ->setSubject($subject)
                                ->send();
                    }

                    if (!empty($vendorUsers)) {
                        foreach ($vendorUsers as $user) {
                            if ($user->profile->phone) {
                                $text = Yii::$app->sms->prepareText('sms.request_add_supplier', [
                                    'client_name' => $client->organization->name
                                ]);
                                Yii::$app->sms->send($text, $user->profile->phone);
                            }
                            if (!empty($user->email)) {
                                $mailer             = Yii::$app->mailer;
                                $subject            = "mixcart.ru - заявка №" . $request->id;
                                $mailer->htmlLayout = 'layouts/request';
                                $mailer->compose('requestInviteSupplier', compact("request", "user"))
                                        ->setTo($user->email)
                                        ->setSubject($subject)
                                        ->send();
                            }
                        }
                    }
                    return ['success' => true];
                }
            }
        }
    }

    /**
     * Закрытие заявки
     * @return array
     */
    public function actionCloseRequest()
    {
        if (Yii::$app->request->isAjax) {
            $user                       = $this->currentUser;
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id                         = Yii::$app->request->post('id');
            if (!Request::find()->where(['rest_org_id' => $user->organization_id, 'id' => $id])->exists()) {
                return ['success' => false];
            }
            $request                = Request::find()->where(['id' => $id])->one();
            $request->active_status = Request::INACTIVE;
            if ($request->save()) {
                return ['success' => true];
            }
        }
    }

    /**
     * Оставить отклик на заявку, отправляет уведомления
     * @return array
     * @throws \Exception
     */
    public function actionAddCallback()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $vendor                     = $this->currentUser;
                $id                         = Yii::$app->request->post('id');
                $price                      = Yii::$app->request->post('price');
                $comment                    = Yii::$app->request->post('comment');

                //Создаем отклик
                $requestCallback               = new RequestCallback();
                $requestCallback->request_id   = $id;
                $requestCallback->supp_org_id  = $vendor->organization_id;
                $requestCallback->supp_user_id = $vendor->id;
                $requestCallback->price        = $price;
                $requestCallback->comment      = $comment;
                //После успешного сохранения, шлем уведомления в ресторан
                if ($requestCallback->save()) {
                    $request  = Request::findOne($id);
                    #Готовим сообщения
                    //Тема Email
                    $text     = Yii::t('app', 'frontend.controllers.request.mix_two', ['ru' => 'mixcart.ru - заявка №%s']);
                    $subject  = sprintf($text, $request->id);
                    //Сообщение SMS
                    $sms_text = Yii::$app->sms->prepareText('sms.request_new_callback', [
                        'request_id'  => $request->id,
                        'vendor_name' => $vendor->organization->name
                    ]);
                    //Найдем всех сотрудников ресторана, кому должны отправить уведомления
                    $clients  = $request->recipientsListForClient;
                    //Если есть клиенты, а они должн быть :)
                    if (!empty($clients)) {
                        foreach ($clients as $client) {
                            //Отправляем смс ресторану о новом отклике
                            if ($client->profile->phone && $client->smsNotification->request_accept == 1) {
                                Yii::$app->sms->send($sms_text, $client->profile->phone);
                            }
                            //Отправляем емайлы ресторану о новом отклике
                            if ($client->email && $client->emailNotification->request_accept == 1) {
                                $mailer             = Yii::$app->mailer;
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
                                'request_accept'  => 1
                            ])->all();
                    //Если есть такие емайлы, шлем туда
                    if (!empty($additional_email)) {
                        $client = User::findOne($request->rest_user_id);
                        foreach ($additional_email as $add_email) {
                            $mailer             = Yii::$app->mailer;
                            $mailer->htmlLayout = 'layouts/request';
                            $mailer->compose('requestNewCallback', compact("request", "client", "vendor"))
                                    ->setTo($add_email->email)
                                    ->setSubject($subject)
                                    ->send();
                        }
                    }
                    //Заносим изменения в базу
                    $transaction->commit();
                    //Возвращаем ответ что все прошло хорошо
                    return ['success' => true];
                } else {
                    throw new \Exception('Не удалось сохранить модель $requestCallback');
                }
            } else {
                throw new \Exception('Только AJAX запросы');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new \Exception($e->getTraceAsString());
        }
    }

}
