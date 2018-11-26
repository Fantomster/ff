<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use api\common\models\one_s\OneSRestAccess;
use api_web\classes\UserWebApi;
use common\components\Mailer;
use common\models\notifications\EmailBlacklist;
use common\models\notifications\EmailFails;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use common\models\Job;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use sizeg\jwt\Jwt;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * User model
 *
 * @inheritdoc
 * @property integer           $organization_id
 * @property integer           $subscribe
 * @property integer           $sms_subscribe
 * @property integer           $send_manager_message
 * @property integer           $send_week_message
 * @property integer           $send_demo_message
 * @property string            $first_logged_at
 * @property string            $language
 * @property integer           $job_id
 * @property string            $email
 * @property integer           $integration_service_id
 * @property Organization      $organization
 * @property FranchiseeUser    $franchiseeUser
 * @property ManagerAssociate  $associated
 * @property EmailNotification $emailNotification
 * @property SmsNotification   $smsNotification
 * @property Job               $job
 * @property EmailQueue        $lastEmail
 */
class User extends \amnah\yii2\user\models\User
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            // general email and username rules
            [['email', 'username', 'language'], 'string', 'max' => 255],
            [['email', 'username'], 'unique', 'on' => ['register', 'admin', 'manage', 'manageNew']],
            [['email', 'username'], 'filter', 'filter' => 'trim'],
            [['email', 'username'], 'trim'],
            [['email'], 'email'],
            [['username'], 'match', 'pattern' => '/^\w+$/u', 'except' => 'social', 'message' => Yii::t('user', '{attribute} can contain only letters, numbers, and "_"')],
            // password rules
            [['newPassword'], 'match', 'pattern' => '/^(?=.*[0-9])([a-zA-Z0-9!@#$%^&*()-_=+\[\]{};:]+)$/'],
            [['newPassword'], 'string', 'min' => 3],
            [['newPassword'], 'required', 'on' => ['register', 'reset', 'acceptInvite', 'manageNew']],
            [['newPasswordConfirm'], 'required', 'on' => ['reset']],
            [['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('app', 'Passwords do not match')],
            // email rules invite client
            [['email'], 'required', 'message' => Yii::t('message', 'frontend.views.vendor.enter_email', ['ru' => 'Введите E-mail'])],
            [['email'], 'required', 'on' => ['sendInviteFromVendor'], 'message' => Yii::t('app', 'common.models.partners_email', ['ru' => 'Введите эл.почту партнера'])],
            [['email'], 'unique', 'on' => ['sendInviteFromVendor2'], 'message' => Yii::t('app', 'common.models.already_exists', ['ru' => 'Пользователь с таким Email уже работает в системе MixCart, пожалуйста, свяжитесь с ним для сотрудничества!'])],
            [['email'], 'validateClient', 'on' => 'sendInviteFromActiveVendor'], // account page
            [['email'], 'validateInviteClient', 'on' => 'sendInviteFromActiveVendor2'], // account page 
            [['currentPassword'], 'validateCurrentPassword', 'on' => ['account']],
            // admin crud rules
            [['role_id', 'status'], 'required', 'on' => ['admin']],
            [['role_id', 'status'], 'integer'],
            [['status', 'first_logged_in_at'], 'safe'],
            [['banned_at'], 'integer', 'on' => ['admin']],
            [['banned_reason'], 'string', 'max' => 255, 'on' => 'admin'],
            [['role_id'], 'required', 'on' => ['manage', 'manageNew']],
            [['organization_id', 'type', 'subscribe', 'sms_subscribe', 'send_manager_message', 'send_week_message', 'send_demo_message'], 'integer'],
            [['organization_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => Organization::className(), 'targetAttribute' => 'id', 'allowArray' => false, 'message' => Yii::t('app', 'common.models.org_not_found', ['ru' => 'Организация не найдена'])],
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

    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if (!$insert && isset($this->oldAttributes['status']) && ($this->oldAttributes['status'] != $this->status) && ($this->status == self::STATUS_ACTIVE) && empty($this->first_logged_in_at)) {
            $this->first_logged_in_at = new Expression('NOW()');
        }
        $this->language = Yii::$app->language;
        return $result;
    }

    /**
     * Confirm user email
     *
     * @param string $newEmail
     * @return bool
     */
    public function confirm($newEmail)
    {
        // update status
        $this->status = static::STATUS_ACTIVE;

        // process $newEmail from a userToken
        //   check if another user already has that email
        $success = true;
        if ($newEmail) {
            $checkUser = static::findOne(["email" => $newEmail]);
            if ($checkUser) {
                $success = false;
            } else {
                $this->email = $newEmail;
            }
        }

        $this->save(false, ["email", "status", "first_logged_in_at"]);
        return $success;
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
//        if(!$insert && isset($changedAttributes['status']) && ($this->status == self::STATUS_ACTIVE) && ($this->first_logged_in_at == null)) {
//            $this->first_logged_in_at = new Expression('NOW()');
//        }

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
            if (empty($smsNotification)) {
                $smsNotification = new notifications\SmsNotification();
            }
            $smsNotification->user_id = $this->id;
            $smsNotification->rel_user_org_id = $this->relationUserOrganization;
            $smsNotification->orders = true;
            $smsNotification->requests = true;
            $smsNotification->changes = true;
            $smsNotification->invites = true;

            $smsNotification->save();
            if ($this->role_id == Role::ROLE_SUPPLIER_MANAGER) {
                $userId = $this->id;
                $organizationId = $this->organization_id;
                $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                if ($clients) {
                    foreach ($clients as $client) {
                        $clientId = $client->rest_org_id;
                        $managerAssociate = new ManagerAssociate();
                        $managerAssociate->manager_id = $userId;
                        $managerAssociate->organization_id = $clientId;
                        $managerAssociate->save();
                    }
                }
            }
        }
        if (!$insert && $this->role_id == Role::ROLE_ONE_S_INTEGRATION) {
            $organizationId = $this->organization_id;
            if ($organizationId) {
                $this->createOneSIntegrationAccount($this->email, $this->password, $this->organization_id);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Set organization id
     *
     * @param      $organization Organization
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

    public function setRole(int $roleId)
    {
        $this->role_id = $roleId;
        $this->save();
        return $this;
    }

    public function setFranchisee(int $fr_id)
    {
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
    public function getOrganization()
    {
        $organization = $this->module->model("Organization");
        return $this->hasOne($organization::className(), ['id' => 'organization_id']);
    }

    public function getOrganizations()
    {
        $organization = $this->module->model("Organization");
        return $this->hasMany($organization::className(), ['id' => 'organization_id'])
            ->viaTable('{{%relation_user_organization}}', ['user_id' => 'id']);
    }

    public function getRelationUserOrganization()
    {
        return $this->hasOne(RelationUserOrganization::className(), ['user_id' => 'id', 'organization_id' => 'organization_id']);
    }

    public function getRelationUserOrganizationRoleID($organizationId)
    {
        $rel = RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $organizationId]);
        return isset($rel->role_id) ? $rel->role_id : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchiseeUser()
    {
        return $this->hasOne(FranchiseeUser::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagersLeader()
    {
        return $this->hasOne(User::className(), ['leader_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssociated()
    {
        return $this->hasMany(ManagerAssociate::className(), ['manager_id' => 'id']);
    }

    /**
     * @param null    $org_id
     * @param boolean $isFranchisee
     * @return EmailNotification|null|static
     */
    public function getEmailNotification($org_id = null, bool $isFranchisee = false)
    {
        return $this->getNotifications('common\models\notifications\EmailNotification', $org_id, $isFranchisee);
    }

    /**
     * @param null $org_id
     * @return SmsNotification|null|static
     */
    public function getSmsNotification($org_id = null, bool $isFranchisee = false)
    {
        return $this->getNotifications('common\models\notifications\SmsNotification', $org_id, $isFranchisee);
    }

    private function getNotifications(String $className, $org_id = null, bool $isFranchisee = false)
    {
        $org_id = ($org_id == null) ? $this->organization_id : $org_id;
        $rel = RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $org_id]);

        if ($rel === null && !$isFranchisee) {
            return $className::emptyInstance();
        }
        if ($rel === null && $isFranchisee) {
            $rel = new RelationUserOrganization();
            $rel->user_id = $this->id;
            $rel->organization_id = $org_id;
            $rel->role_id = $this->role_id;
            $rel->save();
        }
        $res = $className::findOne(['rel_user_org_id' => $rel->id]);
        return ($res != null) ? $res : $className::emptyInstance();
    }

    /**
     * Check if user account is active
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->status == static::STATUS_ACTIVE);
    }

    /**
     * Send email invite to supplier
     *
     * @param User $vendor
     * @return int
     */
    public function sendInviteToVendor($vendor)
    {
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
     * Отправляем Email с приглашением существуюущему вендору
     *
     * @param Request $request
     * @param User    $vendor
     */
    public function sendClientInviteSupplier(User $recipient)
    {
        #Готовим сообщения
        $restoran = $this->organization;
        //var_dump($restoran->name); die();
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        $subject = Yii::t('message', 'frontend.controllers.client.rest_four', ['ru' => "Ресторан "]) . $restoran->name . Yii::t('message', 'frontend.controllers.client.invites_you', ['ru' => " приглашает вас в систему"]);
        $mailer->htmlLayout = $this->module->emailViewPath . '/layouts/html';
        $mailer->compose('clientInviteSupplier', compact("restoran"))
            ->setTo($recipient->email)
            ->setSubject($subject)
            ->send();
        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
    }

    /**
     * Send email invite to restaurant
     *
     * @param User $client
     * @return int
     */
    public function sendInviteToClient($client)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $vendor = $this->organization->name;
        $email = $client->email;
        $subject = Yii::t('app', 'common.models.invitation', ['ru' => "Приглашение на MixCart"]);
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
     *
     * @param User $client
     * @return int
     */
    public function sendInviteToActiveClient($client)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $vendor = $this->organization->name;
        $email = $client->email;
        $subject = Yii::t('app', 'common.models.invitation', ['ru' => "Приглашение на MixCart"]);
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
     *
     * @param string $email
     * @return int
     */
    public function sendInviteToFriend($email)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;
        // send email
        $we = $this->organization->name;
        $subject = Yii::t('app', 'common.models.invitation_two', ['ru' => "Приглашение на MixCart"]);
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
     *
     * @param User $client
     * @return int
     */
    public function sendWelcome()
    {
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
        $subject = Yii::t('app', 'common.models.welcome', ['ru' => "Добро пожаловать на  MixCart"]);
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
     *
     * @param User $user
     * @return int
     */
    public function sendEmployeeConfirmation($user, $isNewConfirm = false)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $profile = $user->profile;

        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;

        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::generate($user->id, $userToken::TYPE_EMAIL_ACTIVATE);
        $email = $user->email;
        $newPassword = $user->newPassword;
        $subject = Yii::t('app', 'common.models.confirm', ['ru' => "Подтвердите аккаунт на MixCart"]);
        $view = $isNewConfirm ? 'confirmEmailTwo' : 'confirmEmail';
        $result = $mailer->compose($view, compact("subject", "user", "profile", "userToken", "newPassword"))
            ->setTo($email)
            ->setSubject($subject)
            ->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    public static function getOrganizationUser($user_ids)
    {
        $user_orgganization = User::find()->select('organization_id')->where(['id' => $user_ids])->one();
        return $user_orgganization['organization_id'];
    }

    /**
     * Send email confirmation to user
     *
     * @param UserToken $userToken
     * @return int
     */
    public function sendEmailConfirmation($userToken)
    {
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
        $subject = Yii::$app->id . " - " . Yii::t("app", 'common.models.user.confirm.', ['ru' => "Подтверждение Email"]);

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
        $clientRoles = [
            Role::ROLE_RESTAURANT_MANAGER,
            Role::ROLE_RESTAURANT_EMPLOYEE,
            Role::ROLE_RESTAURANT_ACCOUNTANT,
            Role::ROLE_RESTAURANT_BUYER,
            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
            Role::ROLE_RESTAURANT_ORDER_INITIATOR
        ];
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
            Role::ROLE_FRANCHISEE_ACCOUNTANT,
            Role::ROLE_ONE_S_INTEGRATION,
            Role::ROLE_RESTAURANT_ACCOUNTANT,
            Role::ROLE_RESTAURANT_BUYER,
            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
            Role::ROLE_RESTAURANT_ORDER_INITIATOR
        ];
    }

    /**
     * Занесен ли Email в черный список
     *
     * @return bool
     */
    public function getEmailInBlackList()
    {
        return (bool)EmailBlacklist::find()->where("email = :e", [':e' => $this->email])->one();
    }

    /**
     * Получаем последний фэйл по емайлу
     *
     * @return array|EmailFails|null|\yii\db\ActiveRecord
     */
    public function getEmailLastFail()
    {
        return EmailFails::find()->where("email = :e", [':e' => $this->email])->orderBy('type DESC, id DESC')->limit(1)->one();
    }

    public function getLastEmail()
    {
        return EmailQueue::find()->where("`to` = :email", [':email' => $this->email])->orderBy('id DESC')->limit(1)->one();
    }

    //-- wtf begin
    public function validateClient($attribute, $params)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (RelationSuppRest::findOne(['rest_org_id' => $this->organization_id, 'supp_org_id' => $currentUser->organization_id]))
            $this->addError($attribute, Yii::t('message', 'common.models.rel_already_exists', ['ru' => 'Ресторан с таким email уже сотрудничает с вами. Проверьте список ваших клиентов!']));
    }

    public function validateInviteClient($attribute, $params)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (RelationSuppRestPotential::findOne(['rest_org_id' => $this->organization_id, 'supp_org_id' => $currentUser->organization_id]))
            $this->addError($attribute, Yii::t('app', 'common.models.already_exists', ['ru' => 'Пользователь с таким Email уже работает в системе MixCart, пожалуйста, свяжитесь с ним для сотрудничества!']));
    }

    //-- wtf end                         что это?!! 

    /**
     * Creating user-organization relations
     */
    public function setRelationUserOrganization(int $organizationID, int $roleID): bool
    {
        if (RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $organizationID])) {
            return false;
        }
        if ($roleID == Role::ROLE_SUPPLIER_MANAGER || $roleID == Role::ROLE_RESTAURANT_MANAGER) {
            $this->createRelationUserOrganization($organizationID, $roleID);
            $organization = $this->organization;
            if (isset($organization) && $organization->parent_id) {
                $children = Organization::findAll(['parent_id' => $organization->parent_id]);
                $children = array_merge($children, Organization::findAll(['id' => $organization->parent_id]));
            } else {
                $children = Organization::findAll(['parent_id' => $organization->id]);
            }
            foreach ($children as $child) {
                $this->createRelationUserOrganization($child->id, $roleID);
            }
        } else {
            $this->createRelationUserOrganization($organizationID, $roleID);
        }
        return true;
    }

    /**
     * Creating 1C integration account
     */
    public function createOneSIntegrationAccount(String $email, String $pass, int $organizationID): bool
    {
        try {
            $apiAccess = OneSRestAccess::findOne(['login' => $email, 'org' => $organizationID]);
            if (!$apiAccess) {
                $apiAccess = new OneSRestAccess();
                $apiAccess->login = $email;
                $apiAccess->fid = $this->id;
                $apiAccess->password = $pass;
                $apiAccess->org = $organizationID;
                $apiAccess->fd = new Expression('NOW()');
                $apiAccess->td = new Expression('NOW() + INTERVAL 15 YEAR');
                $apiAccess->is_active = 1;
                $apiAccess->ver = 1;
                $apiAccess->loadDefaultValues();
                if ($apiAccess->validate()) {
                    $apiAccess->save();
                } else {
                    // validation failed: $errors is an array containing error messages
                    $errors = $apiAccess->errors;
                    Yii::error("<pre>" . print_r($errors, 1) . "</pre>");
                }
            } else {
                $apiAccess->password = $pass;
                $apiAccess->save();
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Creating single user-organization relation
     */
    public function createRelationUserOrganization(int $organizationID, int $roleID): bool
    {
        $check = RelationUserOrganization::findOne(['user_id' => $this->id, 'organization_id' => $organizationID]);
        if ($check) {
            return false;
        }
        $rel = new RelationUserOrganization();
        $rel->user_id = $this->id;
        $rel->organization_id = $organizationID;
        $roleID = self::getRelationRole($roleID, $organizationID);
        $rel->role_id = $roleID;
        $rel->save();
        return $rel->id;
    }

    /**
     * Deleting single user-organization relation
     */
    public function deleteRelationUserOrganization(int $userId, int $organizationId): bool
    {
        $check = RelationUserOrganization::findOne(['user_id' => $userId, 'organization_id' => $organizationId]);
        if ($check) {
            $check->delete();
        }
        return true;
    }

    /**
     * Deleting all user-organization relations
     */
    public static function deleteUserFromOrganization(int $userID, int $organizationID): bool
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            self::deleteRelationUserOrganization($userID, $organizationID);

            $check = RelationUserOrganization::findOne(['user_id' => $userID]);

            if (isset($check)) {
                $existingUser = User::findOne(['id' => $userID]);
                $existingUser->organization_id = $check->organization_id;
                $existingUser->role_id = $check->role_id;
                $existingUser->save();
                $transaction->commit();
                return true;
            } else {
                $result = self::deleteAllUserData($userID);
                if ($result) {
                    $transaction->commit();
                } else {
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
        $children = (new \yii\db\Query())->select(['type_id'])->from('organization')->where(['id' => $organizationID])->one();
        if ($children['type_id'] == Organization::TYPE_RESTAURANT) {
            if ($roleID == Role::ROLE_SUPPLIER_MANAGER) {
                $roleID = Role::ROLE_RESTAURANT_MANAGER;
            }
            if ($roleID == Role::ROLE_SUPPLIER_EMPLOYEE) {
                $roleID = Role::ROLE_RESTAURANT_EMPLOYEE;
            }
        }
        if ($children['type_id'] == Organization::TYPE_SUPPLIER) {
            if ($roleID == Role::ROLE_RESTAURANT_MANAGER) {
                $roleID = Role::ROLE_SUPPLIER_MANAGER;
            }
            if ($roleID == Role::ROLE_RESTAURANT_EMPLOYEE) {
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
            if ($user) {
                EmailNotification::deleteAll(['user_id' => $userID]);
                SmsNotification::deleteAll(['user_id' => $userID]);
                if ($user) {
                    $first = md5(time());
                    $second = md5(rand(1111111, 999999999999));
                    $email = $first . "@" . $second . ".ru";
                    $user->email = $email;
                    $user->organization_id = null;
                    $user->save();
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
    public function updateRelationUserOrganization(int $organizationID, int $roleID): bool
    {
        $relation = RelationUserOrganization::find()->where(['user_id' => $this->id, 'organization_id' => $organizationID])->one();
        if (empty($relation)) {
            return false;
        }
        $relation->role_id = $roleID;
        $relation->save();
        $organization = Organization::findOne(['id' => $organizationID]);
        if ($organization->parent_id) {
            $children = Organization::findAll(['parent_id' => $organization->parent_id]);
            $children = array_merge($children, Organization::findAll(['id' => $organization->parent_id]));
        } else {
            $children = Organization::findAll(['parent_id' => $organization->id]);
        }
        if (($roleID == Role::ROLE_SUPPLIER_MANAGER || $roleID == Role::ROLE_RESTAURANT_MANAGER)) {
            foreach ($children as $child) {
                $this->createRelationUserOrganization($child->id, $roleID);
            }
        } else {
            foreach ($children as $child) {
                self::deleteRelationUserOrganization($this->id, $child->id);
            }
            $this->organization_id = $organizationID;
            $this->role_id = $roleID;
            $this->save();
        }
        $this->createRelationUserOrganization($organizationID, $roleID);
        return true;
    }

    /**
     * Список организаций доступных для пользователя
     *
     * @return array
     */
    public function getAllOrganization($searchString = null, $type = null): array
    {
        $userID = $this->id;
        if ($this->role_id == Role::ROLE_ADMIN || $this->role_id == Role::ROLE_FKEEPER_MANAGER || $this->role_id == Role::ROLE_FRANCHISEE_OWNER || $this->role_id == Role::ROLE_FRANCHISEE_OPERATOR) {
            $org = Organization::findOne(['id' => $this->organization_id]);
            $orgArray = Organization::find()->distinct()->leftJoin(['org2' => 'organization'], 'org2.parent_id=organization.id')->where(['organization.id' => $this->organization_id]);
            if ($searchString) {
                $orgArray = $orgArray->andWhere(['like', 'organization.name', $searchString]);
            }
            $orgArray = $orgArray->orWhere(['organization.parent_id' => $this->organization_id]);
            if ($org && $org->parent_id != null) {
                $orgArray = $orgArray->orWhere(['organization.id' => $org->parent_id])->orWhere(['organization.parent_id' => $org->parent_id]);
            }
            if ($searchString) {
                $orgArray = $orgArray->andWhere(['like', 'organization.name', $searchString]);
            }

            if ($type) {
                $orgArray->andWhere(['organization.type_id' => $type]);
            }

            $orgArray = $orgArray->orderBy('organization.name')->all();
            return $orgArray;
        } else {
            $orgArray = Organization::find()->distinct()->joinWith('relationUserOrganization')->where(['relation_user_organization.user_id' => $userID]);
            if ($searchString) {
                $orgArray = $orgArray->andWhere(['like', 'organization.name', $searchString]);
            }
            if ($type) {
                $orgArray->andWhere(['organization.type_id' => $type]);
            }
            $orgArray = $orgArray->orderBy('organization.name')->all();
            return $orgArray;
        }
    }

    public function getAllOrganizationsDataProvider($searchString = null, $showEmpty = false): ArrayDataProvider
    {
        $dataProvider = new ArrayDataProvider([
            'allModels'  => (new UserWebApi())->getAllOrganization($searchString, $showEmpty),
            'pagination' => [
                'pageSize' => 4,
            ],
        ]);
        return $dataProvider;
    }

    /**
     * Проверка, можно ли переключиться на организацию
     *
     * @param $organization_id
     * @return bool
     */
    public function isAllowOrganization(int $organization_id): bool
    {
        $all = $this->getAllOrganization();
        foreach ($all as $item) {
            if ($item['id'] == $organization_id) {
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
            $userOrgName = isset($vendor->organization) ? $vendor->organization->name : '';

            $result = [
                'success'      => true,
                'eventType'    => 6,
                'message'      => Yii::t('app', 'common.models.already_register', ['ru' => 'Поставщик уже зарегистрирован в системе, Вы можете его добавить нажав кнопку <strong>Пригласить</strong>']),
                'fio'          => $userProfileFullName,
                'phone'        => $userProfilePhone,
                'organization' => $userOrgName,
                'org_id'       => $userOrgId
            ];
        }
        return $result;
    }

    public function wipeNotifications()
    {
        $toBeWiped = [
            'order_created'          => 0,
            'order_canceled'         => 0,
            'order_changed'          => 0,
            'order_processing'       => 0,
            'order_done'             => 0,
            'request_accept'         => 0,
            'receive_employee_email' => 0,
        ];
        $allEmailNotifications = EmailNotification::findAll(['user_id' => $this->id]);
        foreach ($allEmailNotifications as $emailNotification) {
            $emailNotification->load(['EmailNotification' => $toBeWiped]);
            $emailNotification->save();
            $test = 1;
        }
        $allSmsNotifications = SmsNotification::findAll(['user_id' => $this->id]);
        foreach ($allSmsNotifications as $smsNotification) {
            $smsNotification->load(['SmsNotification' => $toBeWiped]);
            $smsNotification->save();
        }
    }
    
    public function setNotifications()
    {
        $toBeSet = [
            'order_created'          => 1,
            'order_canceled'         => 1,
            'order_changed'          => 1,
            'order_processing'       => 1,
            'order_done'             => 1,
            'request_accept'         => 1,
            'receive_employee_email' => 1,
        ];
        $allEmailNotifications = EmailNotification::findAll(['user_id' => $this->id]);
        foreach ($allEmailNotifications as $emailNotification) {
            $emailNotification->load(['EmailNotification' => $toBeSet]);
            $emailNotification->save();
            $test = 1;
        }
        $allSmsNotifications = SmsNotification::findAll(['user_id' => $this->id]);
        foreach ($allSmsNotifications as $smsNotification) {
            $smsNotification->load(['SmsNotification' => $toBeSet]);
            $smsNotification->save();
        }
    }

    /**
     * @param bool $empty
     * @return array
     */
    public static function getMixManagersList($empty = false)
    {
        $managers = self::find()
            ->select(['user.id', 'profile.full_name'])
            ->joinWith('profile')
            ->where(['user.role_id' => Role::ROLE_FKEEPER_MANAGER])
            ->asArray()
            ->all();
        return \yii\helpers\ArrayHelper::map($managers, 'id', 'full_name');
    }

    /**
     * @param Jwt $jwt
     * @return string
     */
    public function getJWTToken(Jwt $jwt)
    {
        if (empty($this->access_token)) {
            $this->auth_key = \Yii::$app->security->generateRandomString();
            $this->access_token = \Yii::$app->security->generateRandomString();
            $this->save();
        }

        $signer = new Sha256();
        return (string)$jwt->getBuilder()
            ->setIssuer('mixcart.ru')
            ->set('access_token', $this->access_token)
            ->sign($signer, $jwt->key)
            ->getToken();
    }

    /**
     * @param Jwt   $jwt
     * @param Token $token
     * @return null|static
     */
    public static function getByJWTToken(Jwt $jwt, Token $token)
    {
        /** @var ValidationData $data */
        $data = Yii::$app->jwt->getValidationData();
        $data->setIssuer('mixcart.ru');
        if ($token->validate($data) && $jwt->verifyToken($token)) {
            return self::findOne(['access_token' => $token->getClaim('access_token')]);
        }
        return null;
    }

}
