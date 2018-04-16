<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use common\models\RelationSuppRest;
use common\models\RelationUserOrganization;
use common\models\Role;
use api_web\models\User;
use common\models\Profile;
use common\models\UserToken;
use api_web\components\Notice;
use common\models\RelationSuppRestPotential;
use common\models\Organization;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class UserWebApi
 * @package api_web\classes
 */
class UserWebApi extends \api_web\components\WebApi
{
    /**
     * @param array $post
     * [
     *      'user' => [
     *          'email' => 'email@email.ru',
     *          'password' => '123123'
     *      ],
     *      'profile' => [
     *          'phone' => '+79276665544'
     *      ],
     *      'organization' => [
     *          'type_id' => 1
     *      ]
     * ]
     * @return string
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function create(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $organization = new Organization (["scenario" => "register"]);
            $organization->load($post, 'organization');

            if ($organization->rating == null or empty($organization->rating) or empty(trim($organization->rating))) {
                $organization->setAttribute('rating', 0);
            }

            if (!$organization->validate()) {
                throw new ValidationException($organization->getFirstErrors());
            }
            $organization->save();

            $user = $this->createUser($post, Role::getManagerRole($organization->type_id));
            $user->setOrganization($organization, true);
            $profile = $this->createProfile($post, $user);

            $userToken = UserToken::generate($user->id, UserToken::TYPE_EMAIL_ACTIVATE);
            Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $profile->phone);
            $transaction->commit();
            return $user->id;
        } catch (ValidationException $e) {
            $transaction->rollBack();
            throw new ValidationException($e->validation);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Создание пользователя
     * @param array $post
     * @param $role_id
     * @return User
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function createUser(array $post, $role_id)
    {
        if (User::findOne(['email' => $post['user']['email']])) {
            throw new BadRequestHttpException('Данный Email уже присутствует в системе.');
        }

        $post['user']['newPassword'] = $post['user']['password'];
        unset($post['user']['password']);

        $user = new User(["scenario" => "register"]);
        $user->load($post, 'user');
        if (!$user->validate()) {
            throw new ValidationException($user->getFirstErrors());
        }
        $user->setRegisterAttributes($role_id)->save();
        return $user;
    }

    /**
     * Создание профиля пользователя
     * @param array $post
     * @param User $user
     * @return Profile
     * @throws ValidationException
     */
    public function createProfile(array $post, User $user)
    {
        $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['profile']['phone']);
        if (mb_substr($phone, 0, 1) == '8') {
            $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
        }

        if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
            throw new ValidationException(['phone' => 'Bad format. (+79112223344)']);
        }

        $profile = new Profile (["scenario" => "register"]);
        $profile->load($post, 'profile');
        if (!$profile->validate()) {
            throw new ValidationException($profile->getFirstErrors());
        }
        $profile->setUser($user->id)->save();
        return $profile;
    }

    /**
     * @param array $post
     * [
     *      'user_id' => 1,
     *      'code' => 2233
     * ]
     * @return string
     * @throws BadRequestHttpException
     */
    public function confirm(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user_id = (int)trim($post['user_id']);
            if (empty($user_id)) {
                throw new BadRequestHttpException('Empty user_id');
            }

            $code = (int)trim($post['code']);
            if (empty($code)) {
                throw new BadRequestHttpException('Empty code');
            }

            $userToken = UserToken::findByPIN($code, [UserToken::TYPE_EMAIL_ACTIVATE]);
            if (!$userToken || ($userToken->user_id !== $user_id)) {
                throw new BadRequestHttpException(\Yii::t('app', 'api.modules.v1.modules.mobile.controllers.wrong_code'));
            }

            $user = User::findOne($user_id);
            $user->setAttribute('status', User::STATUS_ACTIVE);
            $user->save();
            Notice::init('User')->sendEmailWelcome($user);
            $userToken->delete();
            $transaction->commit();
            return $user->access_token;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array $post
     * [
     *      'organization_id' => 12
     * ]
     * @return int
     * @throws BadRequestHttpException
     */
    public function setOrganization(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if (!isset($post['organization_id'])) {
                throw new BadRequestHttpException('Empty organization_id');
            }

            $organization = Organization::findOne(['id' => $post['organization_id']]);
            if (empty($organization)) {
                throw new BadRequestHttpException('Нет организации с таким id');
            }

            if (!$this->user->isAllowOrganization($organization->id)) {
                throw new BadRequestHttpException('Нет прав переключиться на эту организацию');
            }

            $allow_roles = [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER];

            if (in_array($this->user->role_id, $allow_roles) || RelationUserOrganization::checkRelationExisting($this->user)) {
                if (!in_array($this->user->role_id, [Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER])) {
                    $roleID = RelationUserOrganization::getRelationRole($organization->id, $this->user->id);
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        $this->user->role_id = $roleID ?? Role::ROLE_RESTAURANT_MANAGER;
                    }

                    if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                        $this->user->role_id = $roleID ?? Role::ROLE_SUPPLIER_MANAGER;
                    }
                }
                $this->user->organization_id = $organization->id;
            } else if (in_array($this->user->role_id, Role::getFranchiseeEditorRoles())) {
                $this->user->organization_id = $organization->id;
            } else {
                throw new \Exception('access denied');
            }

            $result = $this->user->save();
            $transaction->commit();

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function getAllOrganization()
    {
        $list_organisation = $this->user->getAllOrganization();
        if (empty($list_organisation)) {
            throw new BadRequestHttpException('Нет доступных организаций');
        }

        $result = [];
        foreach ($list_organisation as $item) {
            $model = Organization::findOne($item['id']);
            $result[] = WebApiHelper::prepareOrganization($model);
        }

        return $result;
    }

    /**
     * Список поставщиков пользователя
     * @param array $post
     * @return array
     */
    public function getVendors(array $post)
    {
        $sort = (isset($post['sort']) ? $post['sort'] : null);
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $currentOrganization = $this->user->organization;
        $searchModel = new \common\models\search\VendorSearch();

        $dataProvider = $searchModel->search([], $currentOrganization->id);
        $dataProvider->pagination->setPage($page - 1);
        $dataProvider->pagination->pageSize = $pageSize;

        /**
         * Поиск по статусу поставщика
         */
        if (isset($post['search']['status'])) {
            switch ($post['search']['status']) {
                case 1:
                    $addWhere = ['invite' => 1, 'u.status' => 1];
                    break;
                case 2:
                    $addWhere = ['invite' => 1, 'u.status' => 0];
                    break;
                case 3:
                    $addWhere = ['or',
                        ['invite' => 0, 'u.status' => 1],
                        ['invite' => 0, 'u.status' => 0]
                    ];
                    break;
            }
            if (isset($addWhere)) {
                $dataProvider->query->andFilterWhere($addWhere);
            }
        }

        //Поиск по адресу
        if (isset($post['search']['location'])) {
            if (strstr($post['search']['location'], ':') !== false) {
                $location = explode(':', $post['search']['location']);
                if (is_array($location)) {
                    if (isset($location[0])) {
                        $dataProvider->query->andFilterWhere(['u.country' => $location[0]]);
                    }
                    if (isset($location[1])) {
                        $dataProvider->query->andFilterWhere(['u.locality' => $location[1]]);
                    }
                }
            } else {
                $dataProvider->query->andFilterWhere(
                    ['or',
                        ['u.country' => $post['search']['location']],
                        ['u.locality' => $post['search']['location']]
                    ]
                );
            }
        }

        //$dataProvider->query->andWhere('1=0');
        //Ответ
        $return = [
            'headers' => [],
            'vendors' => [],
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort' => $sort
        ];

        //Сортировка
        if (isset($post['sort'])) {

            $field = $post['sort'];
            $sort = 'ASC';

            if (strstr($post['sort'], '-') !== false) {
                $field = str_replace('-', '', $field);
                $sort = 'DESC';
            }

            if ($field == 'name') {
                $field = 'vendor_name ' . $sort;
            }

            if ($field == 'address') {
                //$field = 'organization.locality ' . $sort;
            }

            if ($field == 'status') {
                switch ($sort) {
                    case 'DESC':
                        $sort = 'ASC';
                        break;
                    case 'ASC':
                        $sort = 'DESC';
                        break;
                }
                $field = "invite {$sort}, `status` {$sort}";
            }

            $dataProvider->query->orderBy($field);
        }
        //Данные для ответа
        foreach ($dataProvider->models as $model) {
            $return['vendors'][] = $this->prepareVendor($model);
        }
        //Названия полей
        if (isset($return['vendors'][0])) {
            foreach (array_keys($return['vendors'][0]) as $key) {
                $return['headers'][$key] = (new Organization())->getAttributeLabel($key);
            }
        }

        return $return;
    }

    /**
     * Список статусов поставщиков
     * @return array
     */
    public function getVendorStatusList()
    {
        return [
            1 => \Yii::t('message', 'frontend.views.client.suppliers.partner'),
            2 => \Yii::t('message', 'frontend.views.client.suppliers.catalog_not_set'),
            3 => \Yii::t('message', 'frontend.views.client.suppliers.send_invite'),
        ];
    }

    /**
     * Список географического расположения поставщиков ресторана
     * @return array
     */
    public function getVendorLocationList()
    {
        $currentOrganization = $this->user->organization;
        $searchModel = new \common\models\search\VendorSearch();
        $dataProvider = $searchModel->search([], $currentOrganization->id);
        $dataProvider->pagination->setPage(0);
        $dataProvider->pagination->pageSize = 1000;

        $return = [];
        $vendor_ids = [];

        $models = $dataProvider->getModels();
        if (!empty($models)) {
            foreach ($models as $model) {
                $vendor_ids[] = $model->supp_org_id;
            }

            $vendor_ids = array_unique($vendor_ids);

            $query = new Query();
            $query->distinct();
            $query->from(Organization::tableName());
            $query->select(['country', 'locality']);
            $query->where(['in', 'id', $vendor_ids]);
            $query->andWhere('country is not null');
            $query->andWhere("country != 'undefined'");
            $query->andWhere("country != ''");
            $query->andWhere('locality is not null');
            $query->andWhere("locality != 'undefined'");
            $query->andWhere("locality != ''");
            $query->orderBy('country');

            $result = $query->all();

            if ($result) {
                foreach ($result as $row) {
                    $return[] = [
                        'title' => $row['country'] . ', ' . $row['locality'],
                        'value' => trim($row['country']) . ':' . trim($row['locality'])
                    ];
                }
            }

        }

        return $return;
    }

    /**
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function removeVendor(array $post)
    {
        if (empty($post['vendor_id'])) {
            throw new BadRequestHttpException('Empty vendor_id');
        }

        $id = (int)$post['vendor_id'];
        $vendor = Organization::find()->where(['id' => $id])->andWhere(['type_id' => Organization::TYPE_SUPPLIER])->one();

        if (empty($vendor)) {
            throw new BadRequestHttpException('Not found vendor');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $where = [
                'rest_org_id' => $this->user->organization->id,
                'supp_org_id' => $vendor->id
            ];

            if (RelationSuppRest::find()->where($where)->exists() || RelationSuppRestPotential::find()->where($where)->exists()) {
                RelationSuppRest::deleteAll($where);
                RelationSuppRestPotential::deleteAll($where);
            } else {
                throw new BadRequestHttpException('Вы не работаете с этим поставщиком');
            }
            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Информация о поставщике
     * @param RelationSuppRest $model
     * @return array
     */
    public function prepareVendor(RelationSuppRest $model)
    {
        $status_list = $this->getVendorStatusList();

        $locality = [
            $model->vendor->country,
            $model->vendor->administrative_area_level_1,
            $model->vendor->locality,
            $model->vendor->route
        ];

        foreach ($locality as $key => $val) {
            if (empty($val) or $val == 'undefined') {
                unset($locality[$key]);
            }
        }

        if ($model->invite == RelationSuppRest::INVITE_ON) {
            if ($model->status == RelationSuppRest::CATALOG_STATUS_ON) {
                $status = $status_list[1];
            } else {
                $status = $status_list[2];
            }
        } else {
            $status = $status_list[3];
        }

        return [
            'id' => (int)$model->vendor->id,
            'name' => $model->vendor->name ?? "",
            'cat_id' => (int)$model->cat_id,
            'email' => $model->vendor->email ?? "",
            'phone' => $model->vendor->phone ?? "",
            'status' => $status,
            'picture' => $model->vendor->getPictureUrl() ?? "",
            'address' => implode(', ', $locality),
            'rating' => $model->vendor->rating ?? 0,
            'allow_editing' => $model->vendor->allow_editing
        ];
    }
}