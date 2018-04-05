<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\AdditionalEmail;
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

        return $this->container->get('MarketWebApi')->prepareOrganization($this->user->organization);
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
            return $this->container->get('MarketWebApi')->prepareOrganization($model);
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
     * Список дополнительных емайл адресов
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function additionalEmailList()
    {
        if ($this->user->organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('This method is forbidden for the vendor.');
        }

        $emails = $this->user->organization->additionalEmail;

        $result = [];
        if (!empty($emails)) {
            $result = $emails;
        }

        return $result;
    }

    /**
     * Обновление дополнительного емайла
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function additionalEmailUpdate(array $post)
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

            if (isset($post['email']) && $model->email != $post['email']) {
                $model->email = $post['email'];
            }

            if ($model->validate() && $model->save()) {
                $t->commit();
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
     */
    public function employeeRoles()
    {
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
     */
    public function employeeList(array $post)
    {
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
        if (empty($post['email'])) {
            throw new BadRequestHttpException('Empty email.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = $this->employeeSearch($post);
            if (!empty($user)) {
                $user_id = $user['id'];
            } else {
                if (empty($post['name'])) {
                    throw new BadRequestHttpException('Empty name.');
                }
                if (empty($post['email'])) {
                    throw new BadRequestHttpException('Empty email.');
                }
                if (empty($post['phone'])) {
                    throw new BadRequestHttpException('Empty phone.');
                }
                if (empty($post['role_id'])) {
                    throw new BadRequestHttpException('Empty role_id.');
                }

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

                /**
                 * @var $user_api UserWebApi
                 */
                $user_api = $this->container->get('UserWebApi');
                //Создаем пользователя
                $user = $user_api->createUser($request, (int)$post['role_id']);
                //Устанавливаем текущую организацию
                $user->setOrganization($this->user->organization, true);
                //Создаем профиль пользователя
                $user_api->createProfile($request, $user);
                $user->refresh();
                $user_id = $user->id;
            }

            if ($relation = RelationUserOrganization::findOne(['user_id' => $user_id, 'organization_id' => $this->user->organization->id])) {
                throw new BadRequestHttpException('Этот сотрудник уже работает под ролью: ' . $relation->user->role->name);
            }

            $relation = new RelationUserOrganization();
            $relation->role_id = $post['role_id'];
            $relation->user_id = $user_id;
            $relation->organization_id = $this->user->organization->id;

            if (!$relation->validate()) {
                throw new ValidationException($relation->getFirstErrors());
            }

            $relation->save();
            $transaction->commit();

            //Тут нужно отправить письмо для смены пароля пользователю
            if ($user instanceof User) {
                //$user->sendEmployeeConfirmation($user);
            }

            return $this->prepareEmployee($relation->user);

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

            if (!empty($post['name'])) {
                $user->profile->full_name = $post['name'];
            }

            if (!empty($post['phone'])) {
                $user->profile->setAttribute('phone', $post['phone']);
            }

            if (!empty($post['email'])) {

                if (User::find()->where(['email' => $post['email']])->exists()) {
                    throw new BadRequestHttpException('Данный Email уже присутствует в системе.');
                }

                $user->email = $post['email'];
            }

            if (!empty($post['role_id'])) {
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
            return [];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param User $model
     * @return array
     */
    private function prepareEmployee(User $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->profile->full_name,
            'email' => $model->email ?? '',
            'phone' => $model->profile->phone ?? '',
            'role' => $model->role->name,
            'role_id' => (int)$model->role->id
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