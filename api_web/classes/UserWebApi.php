<?php

namespace api_web\classes;

use common\models\RelationSuppRest;
use common\models\Role;
use api_web\models\User;
use common\models\Profile;
use common\models\UserToken;
use api_web\components\Notice;
use common\models\Organization;
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
            $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['profile']['phone']);
            if (mb_substr($phone, 0, 1) == '8') {
                $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
            }

            $post['user']['newPassword'] = $post['user']['password'];
            unset($post['user']['password']);

            $user = new User(["scenario" => "register"]);
            $user->load($post, 'user');
            if (!$user->validate()) {
                throw new ValidationException($user->getFirstErrors());
            }

            if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
                throw new ValidationException(['phone' => 'Bad format. (+79112223344)']);
            }

            $profile = new Profile (["scenario" => "register"]);
            $organization = new Organization (["scenario" => "register"]);

            if (User::findOne(['email' => $post['user']['email']])) {
                throw new BadRequestHttpException('Данный Email уже присутствует в системе.');
            }

            $user->setRegisterAttributes(Role::getManagerRole($organization->type_id))->save();

            $profile->load($post, 'profile');
            if (!$profile->validate()) {
                throw new ValidationException($profile->getFirstErrors());
            }
            $profile->setUser($user->id)->save();

            $organization->load($post, 'organization');

            if ($organization->rating == null or empty($organization->rating) or empty(trim($organization->rating))) {
                $organization->setAttribute('rating', 0);
            }

            if (!$organization->validate()) {
                throw new ValidationException($organization->getFirstErrors());
            }
            $organization->save();
            $user->setOrganization($organization, true);

            $userToken = UserToken::generate($user->id, UserToken::TYPE_EMAIL_ACTIVATE);
            Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $user->profile->phone);
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

            if (in_array($this->user->role_id, $allow_roles) || \common\models\RelationUserOrganization::checkRelationExisting($this->user)) {
                if (!in_array($this->user->role_id, [Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER])) {
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        $this->user->role_id = Role::ROLE_RESTAURANT_MANAGER;
                    }

                    if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                        $this->user->role_id = Role::ROLE_SUPPLIER_MANAGER;
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
     * @return mixed
     */
    public function getAllOrganization()
    {
        return $this->user->getAllOrganization();
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

        //Поиск по адресу
        if (isset($post['search']['address'])) {
            $searchModel->search_address = $post['search']['address'];
        }

        $dataProvider = $searchModel->search([], $currentOrganization->id);
        $dataProvider->pagination->setPage($page - 1);
        $dataProvider->pagination->pageSize = $pageSize;

        /**
         * Поиск по статусу поставщика
         */
        if (isset($post['search']['status'])) {
            switch ($post['search']['status']) {
                case 1:
                    $addWhere = ['invite' => 1, 'relation_supp_rest.status' => 1];
                    break;
                case 2:
                    $addWhere = ['invite' => 1, 'relation_supp_rest.status' => 0];
                    break;
                case 3:
                    $addWhere = ['or',
                        ['invite' => 0, 'relation_supp_rest.status' => 1],
                        ['invite' => 0, 'relation_supp_rest.status' => 0]
                    ];
                    break;
            }
            if (isset($addWhere)) {
                $dataProvider->query->andWhere($addWhere);
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
                $field = 'organization.name ' . $sort;
            }

            if ($field == 'address') {
                $field = 'organization.locality ' . $sort;
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
            'id' => $model->vendor->id,
            'name' => $model->vendor->name,
            'cat_id' => $model->cat_id,
            'status' => $status,
            'picture' => $model->vendor->getPictureUrl(),
            'address' => implode(', ', $locality)
        ];
    }
}