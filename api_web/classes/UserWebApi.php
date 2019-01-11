<?php

namespace api_web\classes;

use api_web\components\FireBase;
use api_web\components\Registry;
use api_web\helpers\WebApiHelper;
use api_web\models\ForgotForm;
use common\models\licenses\License;
use common\models\ManagerAssociate;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use common\models\RelationSuppRest;
use common\models\RelationUserOrganization;
use common\models\Role;
use api_web\models\User;
use common\models\Profile;
use common\models\SmsCodeChangeMobile;
use common\models\UserToken;
use api_web\components\Notice;
use common\models\RelationSuppRestPotential;
use common\models\Organization;
use yii\db\Query;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class UserWebApi
 *
 * @package api_web\classes
 */
class UserWebApi extends \api_web\components\WebApi
{

    /**
     * Информация о пользователе
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
    public function get($post)
    {
        if (!empty($post['email'])) {
            $model = User::findOne(['email' => $post['email']]);
        } else {
            $user_id = $post['id'] ?? $this->user->id;
            $model = User::findOne($user_id);
        }

        if (empty($model)) {
            throw new BadRequestHttpException('user_not_found');
        }
        if (empty($model->integration_service_id)) {
            foreach ([Registry::RK_SERVICE_ID, Registry::IIKO_SERVICE_ID] as $serviceId) {
                try {
                    License::checkLicense($this->user->organization_id, $serviceId);
                    $model->setIntegrationServiceId($serviceId);
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return [
            'id'                     => $model->id,
            'email'                  => $model->email,
            'phone'                  => $model->profile->phone,
            'name'                   => $model->profile->full_name,
            'role_id'                => $model->role->id,
            'role'                   => $model->role->name,
            'integration_service_id' => $model->integration_service_id,
        ];
    }

    /**
     * Часовой пояс пользователя
     *
     * @return array
     */
    public function getGmt()
    {
        return ['GMT' => \Yii::$app->request->headers->get('GMT') ?? 0];
    }

    /**
     * Создание пользователя
     *
     * @param array $post
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $organization = new Organization (["scenario" => "register"]);
            $organization->load($post, 'organization');
            $organization->is_allowed_for_franchisee = 0;

            if ($organization->rating == null or empty($organization->rating) or empty(trim($organization->rating))) {
                $organization->setAttribute('rating', 0);
            }

            if (!$organization->validate()) {
                throw new ValidationException($organization->getFirstErrors());
            }
            $organization->save();

            $user = $this->createUser($post, Role::getManagerRole($organization->type_id));
            $user->setOrganization($organization, true);
            $user->setRelationUserOrganization($organization->id, $user->role_id);
            $profile = $this->createProfile($post, $user);

            if (empty($organization->name)) {
                $organization->name = $user->email;
                $organization->save();
            }

            $userToken = UserToken::generate($user->id, UserToken::TYPE_EMAIL_ACTIVATE);
            Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $profile->phone);
            $transaction->commit();

            return $user->id;
        } catch (ValidationException $e) {
            $transaction->rollBack();
            throw new ValidationException($e->validation);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание пользователя
     *
     * @param array   $post
     * @param integer $role_id
     * @param null    $status
     * @return User
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function createUser(array $post, $role_id, $status = null)
    {
        if (User::findOne(['email' => $post['user']['email']])) {
            throw new BadRequestHttpException('This email is already present in the system.');
        }

        $post['user']['newPassword'] = $post['user']['password'];
        unset($post['user']['password']);

        $user = new User(["scenario" => "register"]);
        $user->load($post, 'user');
        if (!$user->validate()) {
            throw new ValidationException($user->getFirstErrors());
        }
        $user->setRegisterAttributes($role_id, $status);
        $user->save();

        return $user;
    }

    /**
     * Создание профиля пользователя
     *
     * @param array $post
     * @param       $user
     * @return Profile
     * @throws ValidationException
     */
    public function createProfile(array $post, $user)
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
     * Повторная отправка СМС с кодом активации пользователя
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\ErrorException
     */
    public function registrationRepeatSms($post)
    {
        WebApiHelper::clearRequest($post);

        $this->validateRequest($post, ['user_id']);

        $model = User::findOne($post['user_id']);
        if (empty($model)) {
            throw new BadRequestHttpException('user_not_found');
        }

        $userToken = UserToken::findByUser($model->id, UserToken::TYPE_EMAIL_ACTIVATE);

        if (empty($userToken)) {
            $userToken = UserToken::generate($model->id, UserToken::TYPE_EMAIL_ACTIVATE, 'attempt|1|' . gmdate("Y-m-d H:i:s"));
        } else {
            if (!empty($userToken->data)) {
                //Какая попытка
                $attempt = explode('|', $userToken->data)[1] ?? 1;
                if ($attempt >= 10) {
                    //Дата последней СМС
                    $update_date = explode('|', $userToken->data)[2] ?? gmdate('Y-m-d H:i:s');
                    //Сколько прошло времени
                    $wait_time = round(strtotime(gmdate('Y-m-d H:i:s')) - strtotime($update_date));
                    if ($wait_time < 300 && $wait_time > 0) {
                        throw new BadRequestHttpException('wait_sms_send|' . (300 - (int)$wait_time));
                    }
                    $attempt = 0;
                }
                $data = implode('|', [
                    'attempt',
                    ($attempt + 1),
                    gmdate("Y-m-d H:i:s")
                ]);
                $userToken = UserToken::generate($model->id, UserToken::TYPE_EMAIL_ACTIVATE, $data);
            }
        }

        $userToken->pin = rand(1000, 9999);
        $userToken->save(false);

        Notice::init('User')->sendSmsCodeToActivate($userToken->getAttribute('pin'), $model->profile->phone);

        return ['result' => 1];
    }

    /**
     * Подтверждение регистрации
     *
     * @param array $post
     * @return string
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function confirm(array $post)
    {
        $this->validateRequest($post, ['user_id', 'code']);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user_id = (int)trim($post['user_id']);
            $code = (int)trim($post['code']);

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

            return $user->getJWTToken();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Выбор бизнеса
     *
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function setOrganization(array $post)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->validateRequest($post, ['organization_id']);

            $organization = Organization::findOne(['id' => $post['organization_id']]);
            if (empty($organization)) {
                throw new BadRequestHttpException('organization not found');
            }

            //Список доступных бизнесов
            if (!$this->user->isAllowOrganization($organization->id)) {
                throw new BadRequestHttpException('No rights to switch to this organization.');
            }

            #Расскоментировать после отказа от первой версии
            //License::checkMixCartLicenseResponse($organization->id);

            $roleID = RelationUserOrganization::getRelationRole($organization->id, $this->user->id);

            if ($roleID != null) {
                if (!in_array($this->user->role_id, [Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER])) {
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        $this->user->role_id = $roleID ?? Role::ROLE_RESTAURANT_MANAGER;
                    }
                    if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                        $this->user->role_id = $roleID ?? Role::ROLE_SUPPLIER_MANAGER;
                    }
                }
                $this->user->organization_id = $organization->id;
            } elseif (in_array($this->user->role_id, Role::getFranchiseeEditorRoles())) {
                $this->user->organization_id = $organization->id;
            } else {
                throw new \Exception('access denied.');
            }

            if (!$this->user->save()) {
                throw new ValidationException($this->user->getFirstErrors());
            }

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Список бизнесов пользователя
     *
     * @param null $searchString
     * @param bool $showEmpty
     * @return array
     * @throws BadRequestHttpException
     */
    public function getAllOrganization($searchString = null, $showEmpty = false): array
    {
        $list_organisation = $this->user->getAllOrganization($searchString);
        if (empty($list_organisation) && !$showEmpty) {
            throw new BadRequestHttpException('No organizations available');
        }

        $orgIds = ArrayHelper::getColumn((array)$list_organisation, 'id');
        $licenses = License::getMixCartLicenses($orgIds);

        $result = [];
        foreach (WebApiHelper::generator($list_organisation) as $model) {
            $item = WebApiHelper::prepareOrganization($model);

            $item['license_is_active'] = false;
            $item['license'] = null;
            if (!empty($licenses[$item['id']])) {
                $item['license_is_active'] = true;
                $item['license'] = $licenses[$item['id']];
            }

            $result[] = $item;
        }
        return $result;
    }

    /**
     * Список поставщиков пользователя
     *
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
                    $addWhere = [
                        'u.invite' => 1,
                        'u.status' => 1
                    ];
                    $dataProvider->query->andFilterWhere(['<>', 'u.cat_id', 0]);
                    break;
                case 2:
                    $addWhere = [
                        'u.invite' => 1,
                        'u.status' => 1,
                        'u.cat_id' => 0
                    ];
                    break;
                case 3:
                    $addWhere = [
                        'or',
                        ['u.invite' => 0, 'u.status' => 1],
                        ['u.invite' => 1, 'u.status' => 0],
                        ['u.invite' => 0, 'u.status' => 0],
                    ];
                    break;
            }
            if (isset($addWhere)) {
                $dataProvider->query->andFilterWhere($addWhere);
            }
        }

        /**
         * Поиск по наименованию
         */
        if (isset($post['search']['name'])) {
            $dataProvider->query->andFilterWhere(['like', 'u.vendor_name', $post['search']['name']]);
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
                $dataProvider->query->andFilterWhere([
                    'or',
                    ['u.country' => $post['search']['location']],
                    ['u.locality' => $post['search']['location']]
                ]);
            }
        }

        //Ответ
        $return = [
            'vendors'    => [],
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ],
            'sort'       => $sort
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
                $field = 'u.vendor_name ' . $sort;
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
                $field = "u.invite {$sort}, u.status {$sort}, u.cat_id {$sort}";
            }
            $dataProvider->query->orderBy($field);
        }

        //Данные для ответа
        if (!empty($dataProvider->models)) {
            $r = new \SplObjectStorage();
            foreach ($dataProvider->models as $model) {
                $r->attach((object)$this->prepareVendor($model));
            }
            $return['vendors'] = $r;
        }

        return $return;
    }

    /**
     * Список статусов поставщиков
     *
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
     *
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
     * Отключить поставщика
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function removeVendor(array $post)
    {
        $this->validateRequest($post, ['vendor_id']);

        $id = (int)$post['vendor_id'];
        $vendor = Organization::find()->where(['id' => $id])->andWhere(['type_id' => Organization::TYPE_SUPPLIER])->one();
        if (empty($vendor)) {
            throw new BadRequestHttpException('vendor_not_found');
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
                throw new BadRequestHttpException('You are not working with this supplier.');
            }
            $transaction->commit();

            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Смена пароля пользователя
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function changePassword($post)
    {
        $this->validateRequest($post, ['password', 'new_password', 'new_password_confirm']);

        if (!$this->user->validatePassword($post['password'])) {
            throw new BadRequestHttpException('bad_old_password');
        }

        if ($post['password'] == $post['new_password']) {
            throw new BadRequestHttpException('same_password');
        }

        $tr = \Yii::$app->db->beginTransaction();
        try {
            $this->user->scenario = 'reset';
            $this->user->newPassword = $post['new_password'];
            $this->user->newPasswordConfirm = $post['new_password_confirm'];

            if (!$this->user->validate(['newPassword'])) {
                throw new BadRequestHttpException('bad_password|' . ForgotForm::generatePassword(8));
            }

            if (!$this->user->validate() || !$this->user->save()) {
                throw new ValidationException($this->user->getFirstErrors());
            }

            $tr->commit();

            return ['result' => true];
        } catch (\Exception $e) {
            $tr->rollBack();
            throw $e;
        }

    }

    /**
     * Смена мобильного номера. Для неподтвержденного юзера свойство $isUnconfirmedUser должно быть true
     *
     * @param $post
     * @param $isUnconfirmedUser
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function mobileChange($post, $isUnconfirmedUser = false)
    {
        WebApiHelper::clearRequest($post);
        $this->validateRequest($post, ['phone']);
        $return = ['result' => true];

        $phone = preg_replace('#(\s|\(|\)|-)#', '', $post['phone']);
        if (mb_substr($phone, 0, 1) == '8') {
            $phone = preg_replace('#^8(\d.+?)#', '+7$1', $phone);
        }
        //Проверяем телефон
        if (!preg_match('#^(\+\d{1,2}|8)\d{3}\d{7,10}$#', $phone)) {
            throw new ValidationException(['phone' => 'bad_format_phone']);
        }

        //Проверяем код, если прилетел
        if (!empty($post['code'])) {
            if (!preg_match('#^\d{4}$#', $post['code'])) {
                throw new ValidationException(['code' => 'bad_format_code']);
            }
        }

        //Присваиваем userID
        $userID = $isUnconfirmedUser ? $post['user']['id'] : $this->user->id;
        //Ищем модель на смену номера
        $model = SmsCodeChangeMobile::findOne(['user_id' => $userID]);
        //Если нет модели, но прилетел какой то код, даем отлуп
        if (empty($model) && !empty($post['code'])) {
            throw new BadRequestHttpException('not_code_to_change_phone');
        }

        //Если нет модели
        if (empty($model)) {
            $model = new SmsCodeChangeMobile();
            $model->phone = $phone;
            $model->user_id = $userID;
        }

        //Даем отлуп если он уже достал выпращивать коды
        if ($model->isNewRecord === false && $model->accessAllow() === false) {
            throw new BadRequestHttpException('wait_sms_send|' . (300 - (int)$model->wait_time));
        }

        //Если код в запросе не пришел, шлем смс и создаем запись
        if (empty($post['code'])) {
            //Если модель не новая, значит уже были попытки отправить смс
            //поэтому мы их просто наращиваем
            if ($model->isNewRecord === false) {
                $model->setAttempt();
            }
            //Генерируем код
            $model->code = rand(1111, 9999);
            //Сохраняем модель
            if ($model->validate() && $model->save()) {
                //Отправляем СМС с кодом
                \Yii::$app->sms->send('Code: ' . $model->code, $model->phone);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } else {
            //Проверяем код
            if ($model->checkCode($post['code'])) {
                //Меняем номер телефона, если все хорошо
                try {
                    $model->changePhoneUser();
                    if ($isUnconfirmedUser) {
                        $return = ['token' => $model->user->getJWTToken()];
                    }
                } catch (\Throwable $e) {
                    \Yii::info($e->getMessage());
                }
            } else {
                throw new BadRequestHttpException('bad_sms_code');
            }
        }

        return $return;
    }

    /**
     * Информация о поставщике
     *
     * @param RelationSuppRest $model
     * @return array
     */
    public function prepareVendor(RelationSuppRest $model)
    {
        $status_list = $this->getVendorStatusList();
        $vendor = $model->vendor;
        $locality = [
            $vendor->country,
            $vendor->administrative_area_level_1,
            $vendor->locality,
            $vendor->route
        ];

        $user = User::findOne(['organization_id' => $vendor->id, 'role_id' => Role::ROLE_SUPPLIER_MANAGER]);

        foreach ($locality as $key => $val) {
            if (empty($val) or $val == 'undefined') {
                unset($locality[$key]);
            }
        }

        if ($model->invite == RelationSuppRest::INVITE_ON && $model->cat_id != 0 && $model->status == RelationSuppRest::CATALOG_STATUS_ON) {
            $status = $status_list[1];
            $enumStatus = 'partner';
        } elseif ($model->cat_id == 0) {
            $status = $status_list[2];
            $enumStatus = 'catalog_not_set';
        } else {
            $status = $status_list[3];
            $enumStatus = 'send_invite';
        }

        if (isset($vendor->buisinessInfo->phone) && !empty($vendor->buisinessInfo->phone)) {
            $phone = $vendor->buisinessInfo->phone;
        } elseif (isset($user->profile->phone)) {
            $phone = $user->profile->phone;
        }

        return [
            'id'            => (int)$vendor->id,
            'name'          => $vendor->name ?? "",
            'contact_name'  => $vendor->contact_name ?? "",
            'inn'           => $vendor->buisinessInfo->inn ?? $vendor->inn ?? null,
            'cat_id'        => (int)$model->cat_id,
            'email'         => $vendor->buisinessInfo->legal_email ?? $vendor->email ?? $user->email ?? '',
            'phone'         => $phone ?? '',
            'status'        => $status,
            'enum_status'   => $enumStatus,
            'picture'       => $vendor->getPictureUrl() ?? "",
            'address'       => implode(', ', $locality),
            'rating'        => $vendor->rating ?? 0,
            'allow_editing' => ($vendor->type_id == Organization::TYPE_SUPPLIER) ? !$vendor->vendor_is_work : $vendor->allow_editing,
            'is_edi'        => $vendor->isEdi(),
        ];
    }

    /**
     * Возвращает GMT из базы, если его нет сохраняет из headers, добавляет плюс к не отрицательному таймзону
     *
     * @return string $gmt
     * */
    public function checkGMTFromDb()
    {
        $gmt = $this->getGmt()['GMT'];

        if (!empty($this->user)) {
            $model = $this->user->organization;
            if (is_null($model->gmt)) {
                $model->gmt = $gmt;
                if ($model->validate()) {
                    $model->save();
                }
            }
            $gmt = $model->gmt;
        }

        if (strpos($gmt, '-') === 0) {
            $return = str_replace('-', '+', $gmt);
        } else {
            $return = '-' . $gmt;
        }

        return $return;
    }

    /**
     * @param string|null $indexByField
     * @param string|null $name
     * @return array
     */
    public function getUserOrganizationBusinessList(string $indexByField = null, string $name = null)
    {
        $resQuery = (new Query())
            ->select(['a.id', 'a.name'])
            ->distinct()
            ->from('organization a')
            ->leftJoin('relation_user_organization b', 'a.id = b.organization_id, (select id, parent_id from organization where id = :orgId) c', [':orgId' => $this->user->organization_id])
            ->where('coalesce(a.parent_id, a.id) = coalesce(c.parent_id, c.id)')
            ->andWhere([
                'b.user_id' => $this->user->id,
                'a.type_id' => 1,
                'b.role_id' => [
                    Role::ROLE_RESTAURANT_MANAGER,
                    Role::ROLE_RESTAURANT_EMPLOYEE,
                    Role::ROLE_RESTAURANT_BUYER,
                    Role::ROLE_ADMIN,
                ]
            ]);

        if (isset($name)) {
            $resQuery->andWhere("a.name LIKE :name", [':name' => '%' . $name . '%']);
        }

        if (!is_null($indexByField)) {
            $resQuery->indexBy($indexByField);
        }
        $res = $resQuery->all();

        $licenses = License::getMixCartLicenses(ArrayHelper::getColumn($res, 'id'));
        foreach ($res as &$item) {
            $item['license_is_active'] = isset($licenses[$item['id']]);
        }

        return ['result' => $res];
    }

    /**
     * @param $user_id
     * @param $organization_id
     * @return array|EmailNotification|null|\yii\db\ActiveRecord
     * @throws BadRequestHttpException
     */
    public function setDefaultEmailNotification($user_id, $organization_id)
    {
        $relation = RelationUserOrganization::find()
            ->select('id')
            ->where(['user_id'         => $user_id,
                     'organization_id' => $organization_id,
                     'is_active'       => 1])
            ->scalar();

        if (!$relation) {
            throw new BadRequestHttpException('no such user relation');
        }

        $notification = EmailNotification::findOne(['user_id' => $user_id, 'rel_user_org_id' => $relation]);

        if (!$notification) {
            $notification = new EmailNotification();
            $notification->user_id = $user_id;
            $notification->rel_user_org_id = $relation;
        }

        $notification->orders = true;
        $notification->requests = true;
        $notification->changes = true;
        $notification->invites = true;
        $notification->order_done = isset($organization) ? (($organization->type_id == Organization::TYPE_SUPPLIER) ? 0 : 1) : 0;
        $notification->save();

        return $notification;
    }

    /**
     * @param $user_id
     * @param $organization_id
     * @return SmsNotification
     * @throws BadRequestHttpException
     */
    public function setDefaultSmsNotification($user_id, $organization_id)
    {
        $relation = RelationUserOrganization::find()
            ->select('id')
            ->where(['user_id'         => $user_id,
                     'organization_id' => $organization_id,
                     'is_active'       => 1])
            ->scalar();

        if (!$relation) {
            throw new BadRequestHttpException('no such user relation');
        }

        $notification = SmsNotification::findOne(['user_id' => $user_id, 'rel_user_org_id' => $relation]);

        if (!$notification) {
            $notification = new SmsNotification();
            $notification->user_id = $user_id;
            $notification->rel_user_org_id = $relation;
        }
        $notification->orders = true;
        $notification->requests = true;
        $notification->changes = true;
        $notification->invites = true;
        $notification->save();

        return $notification;
    }

    /**
     * @param      $user_id
     * @param      $organization_id
     * @param      $profile
     * @param null $associate_org_id
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function initUserOptions($user_id, $organization_id, $profile, $associate_org_id = null)
    {
        $user = \common\models\User::findOne(['id' => $user_id]);
        if (!$user) {
            throw new BadRequestHttpException('user_not_found');
        }

        /** @var Transaction $transaction */
        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            $user->setOrganization($organization_id, true);
            $user->setRelationUserOrganization($organization_id, $user->role_id);

            if ($associate_org_id) {
                if (!ManagerAssociate::find()->where(['manager_id' => $user->id, 'organization_id' => $associate_org_id])->exists()) {
                    $managerAssociate = new ManagerAssociate();
                    $managerAssociate->manager_id = $user->id;
                    $managerAssociate->organization_id = $associate_org_id;
                    $managerAssociate->save();
                }
            }

            $this->setDefaultEmailNotification($user->id, $organization_id);
            $this->setDefaultSmsNotification($user->id, $organization_id);
            $this->createProfile($profile, $user);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * Установка флага принятого соглашения  для текущей органищации
     * Нельзя установить уже "принятое" соглашение в "не принятое"
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function setAgreement($request)
    {
        $org = $this->user->organization;
        $this->validateRequest($request, ['type', 'value']);
        $user_agreement = $org->user_agreement;
        $confidencial_policy = $org->confidencial_policy;
        if (!array_key_exists($request['type'], get_defined_vars())) {
            throw new BadRequestHttpException('user.wrong_agreement_name');
        }
        if ($request['value'] == 0 && ${$request['type']} == 1) {
            throw new BadRequestHttpException('user.cannot_disable_accepted_agreement');
        }
        $org->{$request['type']} = $request['value'];
        if (!$org->save()) {
            throw new ValidationException($org->getFirstErrors());
        }

        return ['result' => $org];
    }
}
