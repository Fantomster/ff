<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use api_web\classes\UserWebApi;
use common\components\Mailer;
use common\models\notifications\EmailBlacklist;
use common\models\notifications\EmailFails;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

/**
 * User model
 *
 * @inheritdoc
 *
 * @property integer $organization_id
 * @property integer $subscribe
 * @property integer $send_manager_message
 * @property string $first_logged_at
 *
 * @property Organization $organization
 * @property FranchiseeUser $franchiseeUser
 * @property ManagerAssociate $associated
 * @property EmailNotification $emailNotification
 * @property SmsNotification $smsNotification
 */
class User extends \amnah\yii2\user\models\User {

    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = [
            // general email and username rules
            [['email', 'username'], 'string', 'max' => 255],
            [['email', 'username'], 'unique', 'on' => ['register', 'admin', 'manage', 'manageNew']],
            [['email', 'username'], 'filter', 'filter' => 'trim'],
            [['email'], 'email'],
            [['username'], 'match', 'pattern' => '/^\w+$/u', 'except' => 'social', 'message' => Yii::t('user', '{attribute} can contain only letters, numbers, and "_"')],
            // password rules
            [['newPassword'], 'string', 'min' => 3],
            [['newPassword'], 'filter', 'filter' => 'trim'],
            [['newPassword'], 'required', 'on' => ['register', 'reset', 'acceptInvite', 'manageNew']],
            [['newPasswordConfirm'], 'required', 'on' => ['reset']],
            [['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('user', 'Passwords do not match')],
            // email rules invite client
            [['email'], 'required', 'on' => ['sendInviteFromVendor'], 'message' => Yii::t('app', 'common.models.partners_email', ['ru'=>'Введите эл.почту партнера'])],
            [['email'], 'unique', 'on' => ['sendInviteFromVendor2'], 'message' => Yii::t('app', 'common.models.already_exists', ['ru'=>'Пользователь с таким Email уже работает в системе MixCart, пожалуйста, свяжитесь с ним для сотрудничества!'])],
            [['email'],'validateClient', 'on'=>'sendInviteFromActiveVendor'],      // account page
            [['email'],'validateInviteClient', 'on'=>'sendInviteFromActiveVendor2'],      // account page
            [['currentPassword'], 'validateCurrentPassword', 'on' => ['account']],
            // admin crud rules
            [['role_id', 'status'], 'required', 'on' => ['admin']],
            [['role_id', 'status'], 'integer', 'on' => ['admin']],
            [['status', 'first_logged_in_at'], 'safe'],
            [['banned_at'], 'integer', 'on' => ['admin']],
            [['banned_reason'], 'string', 'max' => 255, 'on' => 'admin'],
            [['role_id'], 'required', 'on' => ['manage', 'manageNew']],
            [['organization_id', 'type', 'subscribe'], 'integer'],
            [['organization_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => Organization::className(), 'targetAttribute' => 'id', 'allowArray' => false, 'message' => Yii::t('app', 'common.models.org_not_found', ['ru'=>'Организация не найдена'])],
        ];

        // add required for currentPassword on account page
        // only if $this->password is set (might be null from a social login)
        if ($this->password) {
            $rules[] = [['currentPassword'], 'required', 'on' => ['account']];
        }

        // add required rules for email/username depending on module properties
        if ($this->module->requireEmail) {
            $rules[] = ["email", "required"];
        }
        if ($this->module->requireUsername) {
            $rules[] = ["username", "required"];
        }

        return $rules;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if(!$insert && isset($changedAttributes['status']) && ($changedAttributes['status'] == self::STATUS_ACTIVE) && ($this->first_logged_in_at == null)) {
            $this->first_logged_in_at = new Expression('NOW()');
        }

        if ($insert) {
            $organization = $this->organization;
            /**
             * Уведомления по Email
             */
            $emailNotification = new notifications\EmailNotification();
            $emailNotification->user_id = $this->id;
            $emailNotification->rel_user_org_id = $this->relationUserOrganization;
            $emailNotification->orders = true;
            $emailNotification->requests = true;
            $emailNotification->changes = true;
            $emailNotification->invites = true;
            $emailNotification->order_done = isset($organization) ? (($organization->type_id == Organization::TYPE_SUPPLIER) ? 0 : 1) : 0;
            $emailNotification->save();

            /**
             * Уведомления по СМС
             */
            $smsNotification = notifications\SmsNotification::findOne(['user_id' => $this->id]);
            if(empty($smsNotification)) {
                $smsNotification = new notifications\SmsNotification();
            }
            $smsNotification->user_id = $this->id;
            $smsNotification->rel_user_org_id = $this->relationUserOrganization;
            $smsNotification->orders = true;
            $smsNotification->requests = true;
            $smsNotification->changes = true;
            $smsNotification->invites = true;

            $smsNotification->save();
            if($this->role_id == Role::ROLE_SUPPLIER_MANAGER){
                $userId = $this->id;
                $organizationId = $this->organization_id;
                $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                    if ($clients){
                        foreach ($clients as $client){
                            $clientId = $client->rest_org_id;
                            $managerAssociate = new ManagerAssociate();
                            $managerAssociate->manager_id = $userId;
                            $managerAssociate->organization_id = $clientId;
                            $managerAssociate->save();
                        }
                    }

            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Set organization id
     * @param $organization Organization
     * @param bool $first
     * @param bool $notification
     * @return $this
     */
    public function setOrganization($organization, $first = false, $notification = false)
    {
        $this->organization_id = $organization->id;

        if ($first && isset($this->profile->phone)) {
            $organization->phone = $this->profile->phone;
        }
        $organization->save();
        $this->save();

        if ($first || $notification) {
            $smsNotification = notifications\SmsNotification::findOne(['user_id' => $this->id]);
            if ($smsNotification) {
                //Отключаем уведомления по умолчанию для ресторанов
                if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                    $smsNotification->setAttribute('order_created', 0);
                    $smsNotification->setAttribute('order_done', 0);
                }
                //Отключаем уведомления по умолчанию для поставщиков
                if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                    $smsNotification->setAttribute('order_processing', 0);
                    $smsNotification->setAttribute('order_done', 0);
                    $smsNotification->setAttribute('request_accept', 0);
                }
                $smsNotification->save();
            }
        }

        return $this;
    }


    public function setRole(int $roleId){
        $this->role_id = $roleId;
        $this->save();
        return $this;
    }

    public function setFranchisee(int $fr_id) {
        $franchisee = Franchisee::findOne(['id' => $fr_id]);
        if ($franchisee) {
            $franchiseeUser = new FranchiseeUser();
            $franchiseeUser->franchisee_id = $fr_id;
            $franchiseeUser->user_id = $this->id;
            $franchiseeUser->save();
        }
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization() {
        $organization = $this->module->model("Organization");

        return $this->hasOne($organization::className(), ['id' => 'organization_id']);
    }

    public function getOrganizations() {
        $organization = $this->module->model("Organization");
        return $this->hasMany($organization::className(), ['id' => 'organization_id'])
            ->viaTable('{{%relation_user_organization}}', ['user_id' => 'id']);
    }


    public function getRelationUserOrganization(){
        return $this->hasOne(RelationUserOrganization::className(), ['user_id'=>'id', 'organization_id'=>'organization_id']);
    }


    public function getRelationUserOrganizationRoleID(int $userID): int
    {
        $user = self::findIdentity(Yii::$app->user->id);
        $rel = RelationUserOrganization::findOne(['user_id'=>$userID, 'organization_id'=>$user->organization_id]);
        return $rel->role_id;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeUser() {
        return $this->hasOne(FranchiseeUser::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagersLeader() {
        return $this->hasOne(User::className(), ['leader_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssociated() {
        return $this->hasMany(ManagerAssociate::className(), ['manager_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailNotification($org_id = null) {
        $org_id = ($org_id == null) ? $this->organization_id : $org_id;
        $rel = RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $org_id]);
        if ($rel === null) {
            return new EmailNotification();
        }
        $res = EmailNotification::findOne(['rel_user_org_id' => $rel->id]);
        return ($res != null) ? $res : new EmailNotification();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsNotification($org_id = null) {
        $org_id = ($org_id == null) ? $this->organization_id : $org_id;
        $rel = RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $org_id]);
        if ($rel === null)
            return new SmsNotification();
        $res = SmsNotification::findOne(['rel_user_org_id' => $rel->id]);
        return ($res != null) ? $res : new SmsNotification();
    }

    /**
     * Check if user account is active
     * 
     * @return bool
     */
    public function isActive() {
        return ($this->status == static::STATUS_ACTIVE);
    }

    /**
     * Send email invite to supplier
     * @param User $vendor
     * @return int
     */
    public function sendInviteToVendor($vendor) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $restaurant = $this->organization->name;
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::generate($vendor->id, $userToken::TYPE_PASSWORD_RESET);
        $email = $vendor->email;
        $subject = "Приглашение на MixCart";
        $result = $mailer->compose('acceptRestaurantsInvite', compact("subject", "vendor", "userToken", "restaurant"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        //return $result;
    }

    /**
     * Send email invite to restaurant
     * @param User $client
     * @return int
     */
    public function sendInviteToClient($client) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $vendor = $this->organization->name;
        $email = $client->email;
        $subject = Yii::t('app', 'common.models.invitation', ['ru'=>"Приглашение на MixCart"]);
        $result = $mailer->compose('acceptVendorInvite', compact("subject", "client", "vendor"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        //return $result;
    }

    /**
     * Send email invite to restaurant
     * @param User $client
     * @return int
     */
    public function sendInviteToActiveClient($client) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $vendor = $this->organization->name;
        $email = $client->email;
        $subject = Yii::t('app', 'common.models.invitation', ['ru'=>"Приглашение на MixCart"]);
        $result = $mailer->compose('acceptActiveVendorInvite', compact("subject", "client", "vendor"))
            ->setTo($email)
            ->setSubject($subject)
            ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        //return $result;
    }

    /**
     * Send email invite to restaurant
     * @param User $client
     * @return int
     */
    public function sendInviteToFriend($email) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $we = $this->organization->name;
        $subject = Yii::t('app', 'common.models.invitation_two', ['ru'=>"Приглашение на MixCart"]);
        $result = $mailer->compose('friendInvite', compact("subject", "we"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    /**
     * Send welcome email after confirmation
     * @param User $client
     * @return int
     */
    public function sendWelcome() {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        Yii::$app->mailer->htmlLayout = 'layouts/mail';
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $type = $this->organization->type_id;
        $name = $this->profile->full_name;
        $user = $this;
        $subject = Yii::t('app', 'common.models.welcome', ['ru'=>"Добро пожаловать на  MixCart"]);
        $result = $mailer->compose('welcome', compact("subject", "type", "name", "user"))
                ->setTo($this->email)
                ->setSubject($subject)
                ->send();

        if (!is_a(Yii::$app, 'yii\console\Application')) {
//            \api\modules\v1\modules\mobile\components\NotificationHelper::actionConfirm($this);
        }

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    /**
     *  Send confirmation email to your new employee
     *  @param User $user
     *  @return int
     */
    public function sendEmployeeConfirmation($user) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $profile = $user->profile;

        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;

        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::generate($user->id, $userToken::TYPE_EMAIL_ACTIVATE);
        $email = $user->email;
        $subject = Yii::t('app', 'common.models.confirm', ['ru'=>"Подтвердите аккаунт на MixCart"]);
        $result = $mailer->compose('confirmEmail', compact("subject", "user", "profile", "userToken"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    public static function getOrganizationUser($user_ids) {
        $user_orgganization = User::find()->select('organization_id')->where(['id' => $user_ids])->one();
        return $user_orgganization['organization_id'];
    }

    /**
     * Send email confirmation to user
     * @param UserToken $userToken
     * @return int
     */
    public function sendEmailConfirmation($userToken) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;

        // send email
        $user = $this;
        $profile = $user->profile;
        $email = $userToken->data ?: $user->email;
        $subject = Yii::$app->id . " - " . Yii::t("app", 'common.models.user.confirm.', ['ru'=>"Подтверждение Email"]);

        $result = $mailer->compose('confirmEmail', compact("subject", "user", "profile", "userToken"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    public static function getAllowedRoles(int $role_id): array
    {
        $clientRoles = [Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_RESTAURANT_EMPLOYEE];
        $vendorRoles = [Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_SUPPLIER_EMPLOYEE];
        $franchiseeRoles = [Role::ROLE_FRANCHISEE_OWNER, Role::ROLE_FRANCHISEE_OPERATOR, Role::ROLE_FRANCHISEE_ACCOUNTANT];
        if (in_array($role_id, $clientRoles)) {
            return $clientRoles;
        }
        if (in_array($role_id, $vendorRoles)) {
            return $vendorRoles;
        }
        if (in_array($role_id, $franchiseeRoles)) {
            return $franchiseeRoles;
        }
        return [
            Role::ROLE_RESTAURANT_MANAGER,
            Role::ROLE_RESTAURANT_EMPLOYEE,
            Role::ROLE_SUPPLIER_MANAGER,
            Role::ROLE_SUPPLIER_EMPLOYEE,
            Role::ROLE_FRANCHISEE_OWNER,
            Role::ROLE_FRANCHISEE_OPERATOR,
            Role::ROLE_FRANCHISEE_ACCOUNTANT
        ];
    }

    /**
     * Занесен ли Email в черный список
     * @return bool
     */
    public function getEmailInBlackList()
    {
        return (bool)EmailBlacklist::find()->where("email = :e", [':e' => $this->email])->one();
    }

    /**
     * Получаем последний фэйл по емайлу
     * @return array|EmailFails|null|\yii\db\ActiveRecord
     */
    public function getEmailLastFail()
    {
        return EmailFails::find()->where("email = :e", [':e' => $this->email])->orderBy('type DESC, id DESC')->one();
    }

    public function validateClient($attribute, $params)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if(RelationSuppRest::findOne(['rest_org_id' => $this->organization_id, 'supp_org_id' => $currentUser->organization_id]))
            $this->addError($attribute, Yii::t('message', 'common.models.rel_already_exists', ['ru'=>'Ресторан с таким email уже сотрудничает с вами. Проверьте список ваших клиентов!']));
    }

    public function validateInviteClient($attribute, $params)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if(RelationSuppRestPotential::findOne(['rest_org_id' => $this->organization_id, 'supp_org_id' => $currentUser->organization_id]))
            $this->addError($attribute, Yii::t('app', 'common.models.already_exists', ['ru'=>'Пользователь с таким Email уже работает в системе MixCart, пожалуйста, свяжитесь с ним для сотрудничества!']));
    }


    /**
     * Creating user-organization relations
     */
    public function setRelationUserOrganization(int $userID, int $organizationID, int $roleID): bool
    {
        if(RelationUserOrganization::findOne(['user_id'=>$userID, 'organization_id'=>$organizationID])){
            return false;
        }
        if(Yii::$app->user->id && ($roleID == Role::ROLE_SUPPLIER_MANAGER || $roleID == Role::ROLE_RESTAURANT_MANAGER)){
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $organization = Organization::findOne(['id'=>$currentUser->organization_id]);

            self::createRelationUserOrganization($userID, $organizationID, $roleID);
            if($organization->parent_id){
                $children = Organization::findAll(['parent_id'=>$organization->parent_id]);
                $children = array_merge($children, Organization::findAll(['id'=>$organization->parent_id]));
            }else{
                $children = Organization::findAll(['parent_id'=>$organization->id]);
            }
            foreach ($children as $child){
                self::createRelationUserOrganization($userID, $child->id, $roleID);
            }
            return true;
        }else{
            return self::createRelationUserOrganization($userID, $organizationID, $roleID);
        }
    }


    /**
     * Creating single user-organization relation
     */
    public function createRelationUserOrganization(int $userID, int $organizationID, int $roleID): bool
    {
        $check = RelationUserOrganization::findOne(['user_id'=>$userID, 'organization_id'=>$organizationID]);
        if($check){
            return false;
        }
        $rel = new RelationUserOrganization();
        $rel->user_id = $userID;
        $rel->organization_id = $organizationID;
        $roleID = self::getRelationRole($roleID, $organizationID);
        $rel->role_id = $roleID;
        $rel->save();
        return true;
    }


    /**
     * Deleting single user-organization relation
     */
    public function deleteRelationUserOrganization(int $userId, int $organizationId): bool
    {
        $check = RelationUserOrganization::findOne(['user_id'=>$userId, 'organization_id'=>$organizationId]);
        if($check){
            $check->delete();
        }
        return true;
    }


    /**
     * Deleting all user-organization relations
     */
    public function deleteUserFromOrganization(int $userID): bool
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $relationsOrg = RelationUserOrganization::find()->select('organization_id')->where(['user_id'=>Yii::$app->user->id])->all();
            $deleteAll = false;
            $relationsTwo = RelationUserOrganization::find()->select('organization_id')->where(['user_id'=>$userID])->all();

            $orgArray = [];
            foreach ($relationsOrg as $item){
                $orgArray[] = $item->organization_id;
            }
            foreach ($relationsTwo as $one){
                if(!in_array($one->organization_id, $orgArray)){
                    $deleteAll = true;
                }
            }

            if($deleteAll){
                $relations = RelationUserOrganization::find()->where(['user_id'=>Yii::$app->user->id])->all();
                foreach ($relations as $relation) {
                    self::deleteRelationUserOrganization($userID, $relation->organization_id);
                }

            }else{
                $user = User::findIdentity(Yii::$app->user->id);
                self::deleteRelationUserOrganization($userID, $user->organization_id);
            }

            $check = RelationUserOrganization::findOne(['user_id'=>$userID]);

            if($check!=null){
                $existingUser = User::findOne(['id' => $userID]);
                $existingUser->organization_id = $check->organization_id;
                $existingUser->role_id = $check->role_id;
                $existingUser->save();
                $transaction->commit();
                return true;
            }else{
                $result = self::deleteAllUserData($userID);
                if($result){
                    $transaction->commit();
                }else{
                    $transaction->rollBack();
                }
                return $result;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }

        return false;
    }


    /**
     * Getting user role for relation
     */
    private function getRelationRole(int $roleID, int $organizationID): int
    {
        $children = (new \yii\db\Query())->select(['type_id'])->from('organization')->where(['id'=>$organizationID])->one();
        if($children['type_id'] == Organization::TYPE_RESTAURANT){
            if($roleID == Role::ROLE_SUPPLIER_MANAGER){
                $roleID = Role::ROLE_RESTAURANT_MANAGER;
            }
            if($roleID == Role::ROLE_SUPPLIER_EMPLOYEE){
                $roleID = Role::ROLE_RESTAURANT_EMPLOYEE;
            }
        }
        if($children['type_id'] == Organization::TYPE_SUPPLIER){
            if($roleID == Role::ROLE_RESTAURANT_MANAGER){
                $roleID = Role::ROLE_SUPPLIER_MANAGER;
            }
            if($roleID == Role::ROLE_RESTAURANT_EMPLOYEE){
                $roleID = Role::ROLE_SUPPLIER_EMPLOYEE;
            }
        }
        return $roleID;
    }


    /**
     * Deleting user completely
     */
    public function deleteAllUserData(int $userID): bool
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $user = User::findOne(['id' => $userID]);
            if ($user->id == Yii::$app->user->id) {
                return false;
            }
            if ($user) {
                EmailNotification::deleteAll(['user_id' => $userID]);
                SmsNotification::deleteAll(['user_id' => $userID]);
                $user_token = UserToken::findOne(['user_id' => $userID]);
                $profile = $user->profile;
                if ($profile) {
                    $profile->delete();
                }

                if ($user_token) {
                    $user_token->delete();
                }
                if ($user->delete()) {
                    $transaction->commit();
                    return true;
                }
                $transaction->rollBack();
                return false;
            }
            $transaction->rollBack();
            return false;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
        return false;
    }


    /**
     * Updating user-organization relations
     */
    public function updateRelationUserOrganization(int $userID, int $organizationID, int $roleID): bool
    {
        $user = User::findIdentity($userID);
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $relation = RelationUserOrganization::find()->where(['user_id'=>$userID, 'organization_id'=>$organizationID])->one();
        $relation->role_id = $roleID;
        $relation->save();
        $organization = Organization::findOne(['id'=>$currentUser->organization_id]);
        if($organization->parent_id){
            $children = Organization::findAll(['parent_id'=>$organization->parent_id]);
            $children = array_merge($children, Organization::findAll(['id'=>$organization->parent_id]));
        }else{
            $children = Organization::findAll(['parent_id'=>$organization->id]);
        }
        if(Yii::$app->user->id && ($roleID == Role::ROLE_SUPPLIER_MANAGER || $roleID == Role::ROLE_RESTAURANT_MANAGER)){
            foreach ($children as $child){
                self::createRelationUserOrganization($userID, $child->id, $roleID);
            }
        }else{
            foreach ($children as $child) {
                self::deleteRelationUserOrganization($userID, $child->id);
            }
            $user->organization_id = $currentUser->organization->id;
            $user->role_id = $roleID;
            $user->save();
        }
        self::createRelationUserOrganization($userID, $organizationID, $roleID);
        return true;
    }
	
    /**
     * Список организаций доступных для пользователя
     * @return array
     */
    public function getAllOrganization(): array
    {
        $userID = $this->id;
        if($this->role_id == Role::ROLE_ADMIN || $this->role_id == Role::ROLE_FKEEPER_MANAGER || $this->role_id == Role::ROLE_FRANCHISEE_OWNER || $this->role_id == Role::ROLE_FRANCHISEE_OPERATOR){
            $org = Organization::findOne(['id'=>$this->organization_id]);
            $orgArray = Organization::find()->distinct()->leftJoin(['org2'=>'organization'], 'org2.parent_id=organization.id')->where(['organization.id'=>$this->organization_id])->orWhere(['organization.parent_id'=>$this->organization_id]);
            if($org && $org->parent_id != null){
                $orgArray = $orgArray->orWhere(['organization.id'=>$org->parent_id])->orWhere(['organization.parent_id'=>$org->parent_id]);
            }
            return $orgArray->orderBy('organization.name')->all();
        }else{
            return Organization::find()->distinct()->joinWith('relationUserOrganization')->where(['relation_user_organization.user_id'=>$userID])->orderBy('organization.name')->all();
        }

    }


    public function getAllOrganizationsDataProvider(): ArrayDataProvider
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => (new UserWebApi())->getAllOrganization(),
            'pagination' => [
                'pageSize' => 4,
            ],
        ]);
        return $dataProvider;
    }


    /**
     * Проверка, можно ли переключиться на организацию
     * @param $organization_id
     * @return bool
     */
    public function isAllowOrganization(int $organization_id): bool
    {
        $all = $this->getAllOrganization();
        foreach ($all as $item) {
            if($item['id'] == $organization_id){
                return true;
            }
        }
        return false;
    }


    /**
     * Checking if email exists in DB
     */
    public static function checkInvitingUser(string $email): array
    {
        $result = [];
        if (User::find()->select('email')->where(['email' => $email])->exists()) {
            $vendor = User::find()->where(['email' => $email])->one();
            $userProfileFullName = $vendor->profile->full_name;
            $userProfilePhone = $vendor->profile->phone;
            $userOrgId = $vendor->organization_id;
            $userOrgName = $vendor->organization->name;

            $result = [
                'success' => true,
                'eventType' => 6,
                'message' => Yii::t('app', 'common.models.already_register', ['ru' => 'Поставщик уже зарегистрирован в системе, Вы можете его добавить нажав кнопку <strong>Пригласить</strong>']),
                'fio' => $userProfileFullName,
                'phone' => $userProfilePhone,
                'organization' => $userOrgName,
                'org_id' => $userOrgId
            ];
        }
        return $result;
    }

}
