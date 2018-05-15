<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use common\models\AdditionalEmail;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use common\models\Organization;
use common\models\RelationUserOrganization;
use common\models\Role;
use common\models\search\UserSearch;
use common\models\User;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class ClientWebApi
 * @package api_web\classes
 */
class ClientWebApi extends WebApi
{

    /**
     * Детальная информация о ресторане
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function detail()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        return WebApiHelper::prepareOrganization($this->user->organization);
    }

    /**
     * Обновление поставщика
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function detailUpdate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }
        //Поиск ресторана в системе
        $model = Organization::find()->where(['id' => $this->user->organization->id, 'type_id' => Organization::TYPE_RESTAURANT])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Client not found');
        }

        //прошли все проверки, будем обновлять
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if (!empty($post['legal_entity'])) {
                $model->legal_entity = $post['legal_entity'];
            }

            if (!empty($post['about'])) {
                $model->about = $post['about'];
            }

            if (!empty($post['contact_name'])) {
                $model->contact_name = $post['contact_name'];
            }

            if (!empty($post['phone'])) {
                $model->phone = $post['phone'];
            }

            if (!empty($post['email'])) {
                $model->email = $post['email'];
            }

            if (!empty($post['name'])) {
                $model->name = $post['name'];
            }

            if (!empty($post['address'])) {
                if (!empty($post['address']['country'])) {
                    $model->country = $post['address']['country'];
                }
                if (!empty($post['address']['region'])) {
                    $model->administrative_area_level_1 = $post['address']['region'];
                }
                if (!empty($post['address']['locality'])) {
                    $model->locality = $post['address']['locality'];
                    $model->city = $post['address']['locality'];
                }
                if (!empty($post['address']['route'])) {
                    $model->route = $post['address']['route'];
                }
                if (!empty($post['address']['house'])) {
                    $model->street_number = $post['address']['house'];
                }
                if (!empty($post['address']['lat'])) {
                    $model->lat = $post['address']['lat'];
                }
                if (!empty($post['address']['lng'])) {
                    $model->lng = $post['address']['lng'];
                }
                if (!empty($post['address']['place_id'])) {
                    $model->place_id = $post['address']['place_id'];
                }
                unset($post['address']['lat']);
                unset($post['address']['lng']);
                unset($post['address']['place_id']);
                $model->address = implode(', ', $post['address']);
                $model->formatted_address = $model->address;
            }

            if (!$model->validate() || !$model->save()) {
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
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function detailUpdateLogo(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['image_source'])) {
            throw new BadRequestHttpException('Empty image_source');
        }

        //Поиск ресторана в системе
        $model = Organization::find()->where(['id' => $this->user->organization->id, 'type_id' => Organization::TYPE_RESTAURANT])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Client not found');
        }

        //прошли все проверки, будем обновлять
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model->scenario = "settings";
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function additionalEmailCreate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (!isset($post['email'])) {
            throw new BadRequestHttpException('Empty email');
        }

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
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function notificationList()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }
        $result = [];

        $rel = RelationUserOrganization::findOne(['organization_id' => $this->user->organization->id, 'user_id' => $this->user->id]);

        $user_phone = SmsNotification::findOne(['user_id' => $this->user->id, 'rel_user_org_id' => $rel->id]);
        if (!empty($user_phone)) {
            $result[] = [
                'id' => $user_phone->id,
                'value' => $this->user->profile->phone,
                'type' => 'user_phone',
                'order_created' => $user_phone['order_created'],
                'order_canceled' => $user_phone['order_canceled'],
                'order_changed' => $user_phone['order_changed'],
                'order_processing' => $user_phone['order_processing'],
                'order_done' => $user_phone['order_done'],
                'request_accept' => $user_phone['request_accept']
            ];
        }

        $user_email = EmailNotification::findOne(['user_id' => $this->user->id, 'rel_user_org_id' => $rel->id]);
        if (!empty($user_email)) {
            $result[] = [
                'id' => $user_email->id,
                'value' => $this->user->email,
                'type' => 'user_email',
                'order_created' => $user_email['order_created'],
                'order_canceled' => $user_email['order_canceled'],
                'order_changed' => $user_email['order_changed'],
                'order_processing' => $user_email['order_processing'],
                'order_done' => $user_email['order_done'],
                'request_accept' => $user_email['request_accept'],
            ];
        }

        $additional_emails = $this->user->organization->additionalEmail;
        if (!empty($additional_emails)) {
            foreach ($additional_emails as $row) {
                $result[] = [
                    'id' => $row['id'],
                    'value' => $row['email'],
                    'type' => 'additional_email',
                    'order_created' => $row['order_created'],
                    'order_canceled' => $row['order_canceled'],
                    'order_changed' => $row['order_changed'],
                    'order_processing' => $row['order_processing'],
                    'order_done' => $row['order_done'],
                    'request_accept' => $row['request_accept'],
                ];
            }
        }

        return $result;
    }

    /**
     * Обновление уведомления
     * @param array $posts
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function notificationUpdate(array $posts)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        foreach ($posts as $post) {
            if (!isset($post['id'])) {
                throw new BadRequestHttpException('Empty id');
            }

            switch ($post['type']) {
                case 'user_phone':
                    $model = SmsNotification::findOne(['id' => $post['id'], 'rel_user_org_id' => $this->user->organization->id]);
                    break;
                case 'user_email':
                    $model = EmailNotification::findOne(['id' => $post['id'], 'rel_user_org_id' => $this->user->organization->id]);
                    break;
                case 'additional_email':
                    $model = AdditionalEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization->id]);
                    break;
            }

            if (empty($model)) {
                throw new BadRequestHttpException('Model not found.');
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

                if (isset($post['value']) && $model instanceof AdditionalEmail) {
                    $model->email = $post['value'];
                }

                if ($model->validate() && $model->save()) {
                    $t->commit();
                    return $this->notificationList();
                } else {
                    throw new ValidationException($model->getFirstErrors());
                }
            } catch (\Exception $e) {
                $t->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Удаление дополнительного емайла
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function additionalEmailDelete(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (!isset($post['id'])) {
            throw new BadRequestHttpException('Empty id');
        }

        $model = AdditionalEmail::findOne(['id' => $post['id'], 'organization_id' => $this->user->organization->id]);
        if (empty($model)) {
            throw new BadRequestHttpException('Additional email not found.');
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeGet(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id.');
        }
        return $this->prepareEmployee($this->userGet($post['id']));
    }

    /**
     * Поиск сотрудника по email
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeSearch(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['email'])) {
            throw new BadRequestHttpException('Empty email.');
        }

        $model = User::findOne(['email' => $post['email']]);

        if (!empty($model)) {
            return $this->prepareEmployee($model);
        }

        return [];
    }

    /**
     * Список ролей для сотрудников ресторана
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeRoles()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
        $result = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $result[] = [
                    'role_id' => (int)$item->id,
                    'name' => $item->name,
                ];
            }
        }
        return $result;
    }

    /**
     * Список сотрудников в ресторане
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function employeeList(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
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

        $h = new User();
        $return = [
            'headers' => [
                'id' => $h->getAttributeLabel('id'),
                'name' => $h->getAttributeLabel('name'),
                'email' => $h->getAttributeLabel('email'),
                'phone' => $h->getAttributeLabel('phone'),
                'role' => $h->getAttributeLabel('role')
            ],
            'employees' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Добавляем сотрудника
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function employeeAdd(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['email'])) {
            throw new BadRequestHttpException('Empty email.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /**
             * Проверка полей
             */
            if (empty($post['name'])) {
                throw new BadRequestHttpException('Empty name.');
            }
            if (empty($post['email'])) {
                throw new BadRequestHttpException('Empty email.');
            }
            if (empty($post['phone'])) {
                throw new BadRequestHttpException('Empty phone.');
            }
            if (empty($post['role_id']) or !isset($post['role_id'])) {
                throw new BadRequestHttpException('Empty role_id.');
            }

            //Интуем роль
            $role_id = (int)$post['role_id'];
            //Проверка, можно ли проставить эту роль что прислали
            $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
            if (!in_array($post['role_id'], ArrayHelper::map($list, 'id', 'id'))) {
                throw new BadRequestHttpException('Нельзя присвоить эту роль пользователю.');
            }

            //Ищем пользователя
            $user = User::findOne(['email' => $post['email']]);
            if (!empty($user)) {
                //Смотрим, вдруг он уже работает в этом ресторане
                $relation = RelationUserOrganization::findOne(['user_id' => $user->id, 'organization_id' => $this->user->organization->id]);
                if (!empty($relation)) {
                    throw new BadRequestHttpException('Этот сотрудник уже работает под ролью: ' . Role::findOne($relation->role_id)->name);
                }
            } else {
                /**
                 * @var $user_api UserWebApi
                 */
                $user_api = $this->container->get('UserWebApi');
                //Это новый пользователь, идем создавать
                //готовим запрос на создание пользователя
                $request = [
                    'user' => [
                        'email' => $post['email'],
                        'password' => substr(md5(time() . time()), 0, 8)
                    ],
                    'profile' => [
                        'phone' => $post['phone'],
                        'full_name' => $post['name']
                    ]
                ];
                //Создаем пользователя
                $user = $user_api->createUser($request, $role_id);
                //Устанавливаем текущую организацию
                $user->setOrganization($this->user->organization, true);
                //Создаем профиль пользователя
                $user_api->createProfile($request, $user);
                $user->refresh();
            }
            //Создаем связь нового сотрудника с рестораном
            $user->createRelationUserOrganization($user->id, $this->user->organization->id, $role_id);
            //Все хорошо, применяем изменения в базе
            $transaction->commit();
            //Тут нужно отправить письмо для смены пароля пользователю
            //$user->sendEmployeeConfirmation($user);
            return $this->prepareEmployee($user);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Обновляем сотрудника
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function employeeUpdate(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->userGet($post['id']);

            $relation = RelationUserOrganization::findOne([
                'user_id' => $user->id,
                'organization_id' => $this->user->organization->id
            ]);

            if (empty($relation)) {
                throw new BadRequestHttpException('This user is not a member of your staff.');
            }

            if (!empty($post['name'])) {
                $user->profile->full_name = $post['name'];
            }

            if (!empty($post['phone'])) {
                $user->profile->setAttribute('phone', $post['phone']);
            }

            if (!empty($post['email']) && $post['email'] != $user->email) {
                if (User::find()->where(['email' => $post['email']])->exists()) {
                    throw new BadRequestHttpException('Данный Email уже присутствует в системе.');
                }
                $user->email = $post['email'];
            }

            if (!empty($post['role_id'])) {

                $list = Role::find()->where(['organization_type' => Organization::TYPE_RESTAURANT])->all();
                if (!in_array($post['role_id'], ArrayHelper::map($list, 'id', 'id'))) {
                    throw new BadRequestHttpException('Нельзя присвоить эту роль пользователю.');
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

            if (!$relation->validate() || !$relation->save()) {
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function employeeDelete(array $post)
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty id.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->userGet($post['id']);

            $relation = RelationUserOrganization::findOne([
                'user_id' => $user->id,
                'organization_id' => $this->user->organization->id
            ]);

            if ($user->organization->id == $this->user->organization->id) {
                $user->organization_id = null;
                $user->save();
            }

            if (!$relation->delete()) {
                throw new ValidationException($relation->getFirstErrors());
            }

            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param User $model
     * @return array
     * @throws BadRequestHttpException
     */
    private function prepareEmployee(User $model)
    {
        $r = RelationUserOrganization::findOne(['user_id' => $model->id, 'organization_id' => $this->user->organization->id]);

        return [
            'id' => (int)$model->id,
            'name' => $model->profile->full_name,
            'email' => $model->email ?? '',
            'phone' => $model->profile->phone ?? '',
            'role' => Role::getRoleName($r->role_id ?? 0),
            'role_id' => (int)$r->role_id
        ];
    }

    /**
     * Поиск пользователя
     * @param $id
     * @return User
     * @throws BadRequestHttpException
     */
    private function userGet($id)
    {
        $model = User::findOne($id);

        if (empty($model)) {
            throw new BadRequestHttpException('User not found id.');
        }

        $organizations = ArrayHelper::map($model->getAllOrganization(), 'id', 'id');
        if (!in_array($this->user->organization->id, $organizations)) {
            throw new BadRequestHttpException('This user is not a member of your staff.');
        }

        return $model;
    }
}