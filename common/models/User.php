<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use Yii;

/**
 * User model
 *
 * @inheritdoc
 *
 * @property integer $organization_id
 * 
 * @property Organization $organization
 */
class User extends \amnah\yii2\user\models\User {

    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = parent::rules();
        $rules[] = [['newPassword'], 'required', 'on' => ['acceptInvite', 'manageNew']];
        $rules[] = [['role_id'], 'required', 'on' => ['manage', 'manageNew']];
        return $rules;
    }

    /**
     * Set organization id
     * @param int $orgId
     * @return static
     */
    public function setOrganization($orgId) {
        $this->organization_id = $orgId;
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
     * Check if user account is active
     * 
     * @return boolean
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
        $userToken = $userToken::generate($vendor->id, $userToken::TYPE_EMAIL_ACTIVATE);
        $email = $vendor->email;
        $subject = "Приглашение на f-keeper";
        $result = $mailer->compose('acceptRestaurantsInvite', compact("subject", "vendor", "userToken", "restaurant"))
                ->setTo($email)
                ->setSubject($subject)
                ->send();

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
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = $this->module->emailViewPath;

        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::generate($user->id, $userToken::TYPE_EMAIL_ACTIVATE);
        $email = $user->email;
        $subject = "Подтвердите аккаунт на f-keeper";
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

}
