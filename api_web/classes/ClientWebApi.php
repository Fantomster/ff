<?php

namespace api_web\classes;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\helpers\BaseHelper;
use api_web\helpers\WebApiHelper;
use common\models\{
    AdditionalEmail,
    notifications\EmailNotification,
    notifications\SmsNotification,
    Organization,
    RelationUserOrganization,
    Role,
    search\UserSearch,
    User,
    vetis\VetisCountry
};
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class ClientWebApi
 *
 * @package api_web\classes
 */
class ClientWebApi extends WebApi
{
    /**
     * @var BaseHelper
     */
    private $helper;

    /**
     * ClientWebApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->helper = new BaseHelper();
    }

    /**
     * Детальная информация о ресторане
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function detail()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        return WebApiHelper::prepareOrganization($this->user->organization);
    }

    /**
     * Обновление поставщика
     *
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function detailUpdate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }
        //Поиск ресторана в системе
        /**@var Organization $model */
        $model = Organization::find()->where(['id' => $this->user->organization->id, 'type_id' => Organization::TYPE_RESTAURANT])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('client_not_found');
        }
        //прошли все проверки, будем обновлять
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if (!empty($post['nds_country_uuid'])) {
                $vetisCountryModel = VetisCountry::findOne($post['nds_country_uuid']);
                if ($vetisCountryModel) {
                    $model->vetis_country_uuid = $vetisCountryModel->uuid;
                }
            }

            if (\Yii::$app->user->can(Registry::OPERATOR) &&
                !\Yii::$app->user->can(Registry::ADMINISTRATOR_RESTAURANT) &&
                !isset($post['is_allowed_for_franchisee'])
            ) {
                throw new ForbiddenHttpException('Access denied!');
            }

            $this->helper->set($model, $post, ['about', 'contact_name', 'phone', 'email', 'name', 'gmt']);

            if (isset($post['is_allowed_for_franchisee']) && in_array($post['is_allowed_for_franchisee'], [0, 1, true, false])) {
                $model->is_allowed_for_franchisee = (int)$post['is_allowed_for_franchisee'];
            }

            $strAddress = '';

            if (isset($post['address']) && $post['address'] !== null) {
                $this->helper->set($model, $post['address'],
                    ['country', 'locality', 'route', 'lat', 'lng', 'place_id']);
                if (isset($post['address']['country']) && !empty($post['address']['country'])) {
                    $strAddress .= $post['address']['country'];
                }
                if (isset($post['address']['region']) && !empty($post['address']['region'])) {
                    $model->administrative_area_level_1 = $post['address']['region'];
                    $strAddress .= ', ' . $post['address']['region'];
                }
                if (isset($post['address']['locality']) && !empty($post['address']['locality'])) {
                    $model->city = $post['address']['locality'];
                    $strAddress .= ', ' . $post['address']['locality'];
                }
                if (isset($post['address']['route']) && !empty($post['address']['route'])) {
                    $strAddress .= ', ' . $post['address']['route'];
                }
                if (isset($post['address']['house']) && !empty($post['address']['house'])) {
                    $strAddress .= ', ' . $post['address']['house'];
                }
                unset($post['address']['lat']);
                unset($post['address']['lng']);
                unset($post['address']['place_id']);
                $model->formatted_address = $model->address = $strAddress;
            }

            if (!$model->validate($model->getDirtyAttributes()) || !$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }

            $transaction->commit();
            return WebApiHelper::prepareOrganization($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    /**
     * Загрузка логотипа ресторана
     *
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function detailUpdateLogo(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['image_source']);

        //Поиск ресторана в системе
        $model = Organization::find()->where(['id' => $this->user->organization->id, 'type_id' => Organization::TYPE_RESTAURANT])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('client_not_found');
        }

        //прошли все проверки, будем обновлять
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model->scenario = "logo";
            $model->picture = WebApiHelper::convertLogoFile($post['image_source']);

            if (!$model->validate() || !$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }

            $transaction->commit();
            $model->refresh();
            return WebApiHelper::prepareOrganization($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание дополнительного емайла
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function additionalEmailCreate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['email']);

        $t = \Yii::$app->db->beginTransaction();
        try {

            $model = new AdditionalEmail();
            $model->organization_id = $this->user->organization->id;
            $model->email = $post['email'];

            $params = [
                "order_created",
                "order_canceled",
                "order_changed",
                "order_processing",
                "order_done",
                "request_accept"
            ];

            foreach ($params as $param) {
                if (isset($post[$param])) {
                    $model->$param = $post[$param];
                }
            }

            if ($model->validate() && $model->save()) {
                $t->commit();
                $model->refresh();
                return $model->getAttributes();
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Список уведомлений
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function notificationList()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }
        $result = [];
        $isHeadOfOrganisation = (bool)(in_array($this->user->role_id, [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_ADMIN]));

        if ($isHeadOfOrganisation) {
            $searchModel = new UserSearch();
            $params['UserSearch']['organization_id'] = $this->user->organization->id;
            $dataProvider = $searchModel->search($params);
            $users = ArrayHelper::getColumn((array)$dataProvider->models, 'id');
            $rel = RelationUserOrganization::findAll(['organization_id' => $this->user->organization->id, 'user_id' => $users]);
            $relations = ArrayHelper::getColumn((array)$rel, 'id');
        } else {
            $rel = RelationUserOrganization::findOne(['organization_id' => $this->user->organization->id, 'user_id' => $this->user->id]);
            if (empty($rel)) {
                throw new BadRequestHttpException('RelationUserOrganization_not_found');
            }
            $users[] = $this->user->id;
            $relations[] = $rel->id;
        }

        $additional_emails = $this->user->organization->additionalEmail;
        if (!\Yii::$app->user->can(Registry::MANAGER_RESTAURANT)) {
            $users = $this->user->id;
            $additional_emails = [];
        }

        $user_emails = EmailNotification::find()->where(['user_id' => $users, 'rel_user_org_id' => $relations])->orderBy('created_at')->all();
        $user_phones = SmsNotification::find()->where(['user_id' => $users, 'rel_user_org_id' => $relations])->orderBy('created_at')->all();

        if (!empty($user_emails)) {
            foreach ($user_emails as $user_email) {
                $value = $user_email->user->email;
                if ($isHeadOfOrganisation) {
                    $value = $user_email->user->profile->full_name . ": " . $value;
                }

                $result[] = [
                    'id'               => $user_email->id,
                    'value'            => $value,
                    'user_id'          => $user_email->user->id,
                    'type'             => 'user_email',
                    'order_created'    => $user_email['order_created'],
                    'order_canceled'   => $user_email['order_canceled'],
                    'order_changed'    => $user_email['order_changed'],
                    'order_processing' => $user_email['order_processing'],
                    'order_done'       => $user_email['order_done'],
                    'request_accept'   => $user_email['request_accept'],
                ];
            }
        }

        if (!empty($user_phones)) {
            /** @var SmsNotification $user_phone */
            foreach ($user_phones as $user_phone) {
                $value = $user_phone->user->profile->phone;
                if ($isHeadOfOrganisation) {
                    $value = $user_phone->user->profile->full_name . ": " . $value;
                }
                $result[] = [
                    'id'               => $user_phone->id,
                    'value'            => $value,
                    'user_id'          => $user_phone->user->id,
                    'type'             => 'user_phone',
                    'order_created'    => $user_phone['order_created'],
                    'order_canceled'   => $user_phone['order_canceled'],
                    'order_changed'    => $user_phone['order_changed'],
                    'order_processing' => $user_phone['order_processing'],
                    'order_done'       => $user_phone['order_done'],
                    'request_accept'   => $user_phone['request_accept']
                ];
            }
        }

        ArrayHelper::multisort($result, 'user_id', SORT_ASC, SORT_REGULAR);

        if (!empty($additional_emails)) {
            foreach ($additional_emails as $row) {
                $result[] = [
                    'id'               => $row['id'],
                    'value'            => $row['email'],
                    'type'             => 'additional_email',
                    'order_created'    => $row['order_created'],
                    'order_canceled'   => $row['order_canceled'],
                    'order_changed'    => $row['order_changed'],
                    'order_processing' => $row['order_processing'],
                    'order_done'       => $row['order_done'],
                    'request_accept'   => $row['request_accept'],
                ];
            }
        }

        return $result;
    }

    /**
     * Обновление уведомления
     *
     * @param array $posts
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function notificationUpdate(array $posts)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        foreach ($posts as $post) {
            $this->validateRequest($post, ['id']);

            switch ($post['type']) {
                case 'user_phone':
                    $model = SmsNotification::findOne(['id' => $post['id']]);
                    break;
                case 'user_email':
                    $model = EmailNotification::findOne(['id' => $post['id']]);
                    break;
                case 'additional_email':
                    $model = AdditionalEmail::findOne(['id' => $post['id']]);
                    break;
            }

            if (empty($model)) {
                throw new BadRequestHttpException('model_not_found');
            }

            //Если пользователь не руководитель, он обновлять может только свои уведомления
            if (!in_array($this->user->role_id, [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_ADMIN])) {
                $rel = RelationUserOrganization::findOne(['user_id' => $this->user->id, 'organization_id' => $this->user->organization->id]);
                if (empty($rel)) {
                    throw new BadRequestHttpException('RelationUserOrganization_not_found');
                }

                if ($model instanceof AdditionalEmail) {
                    /** @var AdditionalEmail $model */
                    if ($model->organization_id != $this->user->organization->id) {
                        throw new BadRequestHttpException('model_not_found');
                    }
                } else {
                    /** @var SmsNotification $model */
                    if ($model->user_id != $this->user->id) {
                        throw new BadRequestHttpException('model_not_found');
                    }
                    if ($model->rel_user_org_id != $rel->id) {
                        throw new BadRequestHttpException('model_not_found');
                    }
                }
            }

            $t = \Yii::$app->db->beginTransaction();
            try {
                $params = [
                    "order_created",
                    "order_canceled",
                    "order_changed",
                    "order_processing",
                    "order_done",
                    "request_accept"
                ];

                foreach ($params as $param) {
                    if (isset($post[$param]) && in_array($post[$param], [0, 1])) {
                        $model->$param = $post[$param];
                    }
                }

                if ($model->validate() && $model->save()) {
                    $t->commit();
                } else {
                    throw new ValidationException($model->getFirstErrors());
                }
            } catch (\Exception $e) {
                $t->rollBack();
                throw $e;
            }
        }

        return $this->notificationList();
    }

    /**
     * Удаление дополнительного емайла
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function additionalEmailDelete(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['id']);

        $model = AdditionalEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization->id]);
        if (empty($model)) {
            throw new BadRequestHttpException('additional_email.not_found');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($model->delete()) {
                $t->commit();
                return ['result' => true];
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * Поиск сотрудника по id
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeGet(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['id']);

        return $this->prepareEmployee($this->userGet($post['id']));
    }

    /**
     * Поиск сотрудника по email
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeSearch(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['email']);

        $model = User::findOne(['email' => $post['email']]);

        if (!empty($model)) {
            return $this->prepareEmployee($model);
        }

        return [];
    }

    /**
     * Список ролей для сотрудников ресторана
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeRoles()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
        $result = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $result[] = [
                    'role_id' => (int)$item->id,
                    'name'    => $item->name,
                ];
            }
        }
        return $result;
    }

    /**
     * Список сотрудников в ресторане
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeList(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $attr = new User();

        $headers = [
            'headers' => [
                'id'    => $attr->getAttributeLabel('id'),
                'name'  => $attr->getAttributeLabel('name'),
                'email' => $attr->getAttributeLabel('email'),
                'phone' => $attr->getAttributeLabel('phone'),
                'role'  => $attr->getAttributeLabel('role')
            ],
        ];

        if (!\Yii::$app->user->can(Registry::ADMINISTRATOR_RESTAURANT)) {
            return ArrayHelper::merge($headers, [
                'employees'  => [$this->prepareEmployee($this->user)],
                'pagination' => [
                    'page'       => 1,
                    'page_size'  => 1,
                    'total_page' => 1
                ]
            ]);
        }

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $searchModel = new UserSearch();

        if (isset($post['search'])) {
            $searchModel->searchString = $post['search'];
        }

        $params['UserSearch']['organization_id'] = $this->user->organization->id;
        $dataProvider = $searchModel->search($params);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $models = $dataProvider->models;

        $result = [];

        if (!empty($models)) {
            foreach ($models as $model) {
                $result[] = $this->prepareEmployee($model);
            }
        }

        $return = [
            'employees'  => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return ArrayHelper::merge($headers, $return);
    }

    /**
     * Добавляем сотрудника
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function employeeAdd(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /**
             * Проверка полей
             */
            $this->validateRequest($post, ['email', 'name', 'phone', 'role_id']);

            //Интуем роль
            $role_id = (int)$post['role_id'];
            //Проверка, можно ли проставить эту роль что прислали
            $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
            if (!in_array($post['role_id'], ArrayHelper::map($list, 'id', 'id'))) {
                throw new BadRequestHttpException('user.role_set_access');
            }

            //Ищем пользователя
            $user = User::findOne(['email' => $post['email']]);
            if (!empty($user)) {
                //Смотрим, вдруг он уже работает в этом ресторане
                $relation = RelationUserOrganization::findOne(['user_id' => $user->id, 'organization_id' => $this->user->organization->id]);
                if (!empty($relation)) {
                    throw new BadRequestHttpException('user.work_in_role|' . Role::findOne($relation->role_id)->name);
                }
            } else {
                /**
                 * @var $user_api UserWebApi
                 */
                $user_api = new UserWebApi();
                //Это новый пользователь, идем создавать
                //готовим запрос на создание пользователя
                $request = [
                    'user'    => [
                        'email'    => $post['email'],
                        'password' => substr(md5(time() . time()), 0, 8)
                    ],
                    'profile' => [
                        'phone'     => $post['phone'],
                        'full_name' => $post['name']
                    ]
                ];
                //Создаем пользователя
                $user = $user_api->createUser($request, $role_id, User::STATUS_ACTIVE);
                //Устанавливаем текущую организацию
                $user->setOrganization($this->user->organization, true);
                //Создаем профиль пользователя
                $user_api->createProfile($request, $user);
                $user->refresh();
            }
            //Создаем связь нового сотрудника с рестораном
            $user->createRelationUserOrganization($this->user->organization->id, $role_id);
            //Все хорошо, применяем изменения в базе
            $transaction->commit();
            //Тут нужно отправить письмо для смены пароля пользователю
            $user->sendEmployeeConfirmation($user, true);
            return $this->prepareEmployee($user);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Обновляем сотрудника
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function employeeUpdate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['id']);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->userGet($post['id']);
            if ($user->id != $this->user->id) {
                if (!in_array($this->user->role_id, [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_ADMIN])) {
                    throw new BadRequestHttpException('user.employee.update.access_denied');
                }
            }

            $relation = RelationUserOrganization::findOne([
                'user_id'         => $user->id,
                'organization_id' => $this->user->organization->id
            ]);

            if (empty($relation)) {
                throw new BadRequestHttpException('user.not_staff');
            }

            if (!empty($post['name'])) {
                $user->profile->full_name = $post['name'];
            }

            if (!empty($post['phone'])) {
                $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['phone']);
                if (mb_substr($phone, 0, 1) == '8') {
                    $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
                }
                if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
                    throw new ValidationException(['phone' => 'Bad format. (+79112223344)']);
                }
                $user->profile->setAttribute('phone', $phone);
            }

            if (!empty($post['role_id'])) {

                $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
                if (!in_array($post['role_id'], ArrayHelper::map($list, 'id', 'id'))) {
                    throw new BadRequestHttpException('user.role_set_access');
                }

                $user->role_id = $post['role_id'];
                $relation->role_id = $user->role_id;
            }

            //Валидация и сохранение
            if (!$user->validate() || !$user->save()) {
                throw new ValidationException($user->getFirstErrors());
            }

            if (!$user->profile->validate() || !$user->profile->save()) {
                throw new ValidationException($user->profile->getFirstErrors());
            }

            if (!$relation->save()) {
                throw new ValidationException($relation->getFirstErrors());
            }

            $transaction->commit();
            return $this->prepareEmployee($user);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Удаляем сотрудника
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function employeeDelete(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('method_access_to_vendor');
        }

        $this->validateRequest($post, ['id']);

        if ($post['id'] === $this->user->id) {
            throw new BadRequestHttpException('user.delete_myself');
        }

        if ($this->user->role_id != Role::ROLE_RESTAURANT_MANAGER) {
            throw new BadRequestHttpException('user.employee.delete.access_denied');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->userGet($post['id']);

            $relation = RelationUserOrganization::findOne([
                'user_id'         => $user->id,
                'organization_id' => $this->user->organization->id
            ]);

            if (isset($user->organization->id) && $user->organization->id == $this->user->organization->id) {
                $user->organization_id = null;
                $user->save();
            }

            if (!empty($relation)) {
                if (!$relation->delete()) {
                    throw new ValidationException($relation->getFirstErrors());
                }
            }

            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Список стран для настройки НДС
     *
     * @return array
     */
    public function ndsCountryList()
    {
        $order = "CASE 
            WHEN uuid = :selected_uuid THEN 0
            WHEN name = :name THEN 1
            ELSE name 
        END";
        $models = VetisCountry::find()
            ->select(['uuid', 'name'])
            ->where(['active' => 1])
            ->orderBy(new Expression($order, [
                ':name'          => 'Российская Федерация',
                ':selected_uuid' => $this->user->organization->vetis_country_uuid
            ]))
            ->asArray()
            ->all();

        return ['items' => $models];
    }

    /**
     * @param User $model
     * @return array
     */
    private function prepareEmployee(User $model)
    {
        $r = RelationUserOrganization::findOne(['user_id' => $model->id, 'organization_id' => $this->user->organization->id]);

        return [
            'id'      => (int)$model->id,
            'name'    => $model->profile->full_name,
            'email'   => $model->email ?? '',
            'phone'   => $model->profile->phone ?? '',
            'role'    => Role::getRoleName($r->role_id ?? 0),
            'role_id' => (int)$r->role_id
        ];
    }

    /**
     * Поиск пользователя
     *
     * @param $id
     * @return User
     * @throws BadRequestHttpException
     */
    private function userGet($id)
    {
        $model = User::findOne($id);

        if (empty($model)) {
            throw new BadRequestHttpException('user_not_found');
        }

        $organizations = ArrayHelper::map($model->getAllOrganization(), 'id', 'id');
        if (!in_array($this->user->organization->id, $organizations)) {
            throw new BadRequestHttpException('user.not_staff');
        }

        return $model;
    }
}
