<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use common\models\notifications\EmailBlacklist;
use common\models\notifications\EmailFails;
use Yii;

/**
 * User model
 *
 * @inheritdoc
 *
 * @property integer $organization_id
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
            [['email'], 'unique', 'on' => ['sendInviteFromVendor'], 'message' => Yii::t('app', 'common.models.already_exists', ['ru'=>'Пользователь с таким Email уже работает в системе MixCart, пожалуйста, свяжитесь с ним для сотрудничества!'])],
            // account page
            [['currentPassword'], 'validateCurrentPassword', 'on' => ['account']],
            // admin crud rules
            [['role_id', 'status'], 'required', 'on' => ['admin']],
            [['role_id', 'status'], 'integer', 'on' => ['admin']],
            [['banned_at'], 'integer', 'on' => ['admin']],
            [['banned_reason'], 'string', 'max' => 255, 'on' => 'admin'],
            [['role_id'], 'required', 'on' => ['manage', 'manageNew']],
            [['organization_id'], 'integer'],
            [['organization_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => Organization::className(), 'targetAttribute' => 'id', 'message' => Yii::t('app', 'common.models.org_not_found', ['ru'=>'Организация не найдена'])],
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

    public function afterSave($insert, $changedAttributes) {
        if ($insert) {
            $emailNotification = new notifications\EmailNotification();
            $emailNotification->user_id = $this->id;
            $emailNotification->orders = true;
            $emailNotification->requests = true;
            $emailNotification->changes = true;
            $emailNotification->invites = true;
            $emailNotification->save();
            $smsNotification = new notifications\SmsNotification();
            $smsNotification->user_id = $this->id;
            $smsNotification->orders = true;
            $smsNotification->requests = true;
            $smsNotification->changes = true;
            $smsNotification->invites = true;
            $smsNotification->save();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Set organization id
     * @param int $orgId
     * @return static
     */
    public function setOrganization($organization, $first = false) {
        $this->organization_id = $organization->id;

//        if (isset($this->email)) {
//            $organization->email = $this->email;
//        }
        if ($first && isset($this->profile->phone)) {
            $organization->phone = $this->profile->phone;
        }
        $organization->save();
        $this->save();
        return $this;
    }

    public function setFranchisee($fr_id) {
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
    public function getEmailNotification() {
        return $this->hasOne(notifications\EmailNotification::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmsNotification() {
        return $this->hasOne(notifications\SmsNotification::className(), ['user_id' => 'id']);
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
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $type = $this->organization->type_id;
        $name = $this->profile->full_name;
        $subject = Yii::t('app', 'common.models.welcome', ['ru'=>"Добро пожаловать на  MixCart"]);
        $result = $mailer->compose('welcome', compact("subject", "type", "name"))
                ->setTo($this->email)
                ->setSubject($subject)
                ->send();

        if (!is_a(Yii::$app, 'yii\console\Application')) {
            \api\modules\v1\modules\mobile\components\NotificationHelper::actionConfirm($this->email, $this->id);
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
        $subject = Yii::$app->id . " - " . Yii::t("user", "Email Confirmation");

        $result = $mailer->compose('confirmEmail', compact("subject", "user", "profile", "userToken"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    public static function getAllowedRoles($role_id) {
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

}
