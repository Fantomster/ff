<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\TestVendors;
use Google\Spreadsheet\Exception\BadRequestException;
use Yii;
use api\modules\v1\modules\mobile\models\User;
use api\modules\v1\modules\mobile\resources\User as UserResource;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use common\models\Profile;
use common\models\Organization;
use common\models\Role;
use common\models\UserToken;
use common\models\UserFcmToken;
use yii\data\SqlDataProvider;
use api_web\classes\UserWebApi;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UserController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\User';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }
    
     /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
        ];
    }

    public function actionAuth() {
        
        $user = User::findOne(Yii::$app->user->id);
        $profile = $user->profile;
        $organization = $user->organization;
        //$organization->picture = $organization->pictureUrl;
        return compact("user","profile","organization");
    }
    
     public function actionAvatar($name) {
         $organization = new Organization();  
         $organization->picture = $name;
         header('Content-type: image/jpeg');
         echo file_get_contents($organization->pictureUrl);
  
    }
    
    public function actionRegistration() {
        
        $user = new User(["scenario" => "register"]);
        $profile = new Profile (["scenario" => "register"]);
        $organization = new Organization (["scenario" => "register"]);

        //$user->setScenario("register");
        // load post data
        $post = Yii::$app->request->post();
        if ($user->load($post, 'user') && $user->validate()) {
            // ensure profile data gets loaded
            
            $profile->load($post, 'profile');

            // load organization data
            $organization->load($post,'organization');

            // validate for normal request
            if ($profile->validate() && $organization->validate()) {

                // perform registration
                $role = new Role();

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->setRegisterAttributes($role::getManagerRole($organization->type_id))->save();
                    $profile->setUser($user->id)->save();
                    $organization->save();
                    $user->setOrganization($organization, true)->save();
                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }
                if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                    //$this->initDemoData($user, $profile, $organization);
                }

                if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                    TestVendors::setGuides($organization);
                }

                $user = User::findOne($user->id);
                $this->afterRegister($user);
                return ['success' => 1];  
            }
            elseif(!$profile->validate())
                    $profile_errors = $profile->getErrors();
                else
                    $organization_errors = $organization->getErrors();
        }else
        {
            $user_errors = $user->getErrors();
        }
        return compact("user", "profile", "organization", "user_errors", "profile_errors","organization_errors");
    }
    
    /**
     * Process data after registration
     * @param \amnah\yii2\user\models\User $user
     */
    protected function afterRegister($user)
    {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        $userToken = new UserToken();
        
        // determine userToken type to see if we need to send email
        $userTokenType = null;
        if ($user->status == $user::STATUS_INACTIVE) {
            $userTokenType = $userToken::TYPE_EMAIL_ACTIVATE;
        } elseif ($user->status == $user::STATUS_UNCONFIRMED_EMAIL) {
            $userTokenType = $userToken::TYPE_EMAIL_CHANGE;
        }

        // check if we have a userToken type to process, or just log user in directly
        if ($userTokenType) {
            $userToken = $userToken::generate($user->id, $userTokenType);
            $user->sendEmailConfirmation($userToken);
        } 
    }
    
     /**
     * Confirm email
     */
    public function actionConfirm($pin) {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        /** @var \amnah\yii2\user\models\User $user */
        // search for userToken
        $success = false;
        $email = "";
        $userToken = new UserToken();
        $userToken = $userToken::findByPIN($pin, [$userToken::TYPE_EMAIL_ACTIVATE, $userToken::TYPE_EMAIL_CHANGE]);
        if ($userToken) {

            // find user and ensure that another user doesn't have that email
            //   for example, user registered another account before confirming change of email
            $user = new User();
            $user = $user::findOne($userToken->user_id);
            $newEmail = $userToken->data;
            if ($user->confirm($newEmail)) {
                $success = true;
                $profile = $user->profile;
                $organization = $user->organization;
            }
            if ($userToken->type == $userToken::TYPE_EMAIL_ACTIVATE) {
                //send welcome
                $user->sendWelcome();
                 \api\modules\v1\modules\mobile\components\notifications\NotificationUser::actionConfirm($user);
            }
            // set email and delete token
            $email = $newEmail ? : $user->email;
            $userToken->delete();
        }

        return ($success) ? compact("user","profile","organization") : ['error' => Yii::t('app', 'api.modules.v1.modules.mobile.controllers.wrong_code', ['ru'=>'Неверный код'])/*Yii::t('user','Invalid PIN')*/];
    }

    public function actionCompleteRegistration() {
        $user = Yii::$app->user->identity;
        $profile = new Profile();
        $profile = $user->profile;
        $profile->scenario = "complete";
        $organization = $user->organization;
        $organization->scenario = "complete";

        $post = Yii::$app->request->post();
        $profile->load($post, 'profile');
        $organization->load($post, 'organization');
        
        if ($profile->validate() && $organization->validate()) {
                $profile->save();
                $organization->save();
                return ['success' => 1];
            }
             elseif(!$profile->validate())
                    $profile_errors = $profile->getErrors();
                else
                    $organization_errors = $organization->getErrors();

        return compact("profile", "organization", "profile_errors","organization_errors");
    }
    
    /**
     * Forgot password
     */
    public function actionForgot()
    {
        // load post data and send email
        $model =  new \api\modules\v1\modules\mobile\models\ForgotForm();
        $model->email = Yii::$app->request->post('email');

        if ($model->sendForgotEmail()) {
            return ['success' => 1];
        }
        $email_errors =  $model->getErrors();   
        return compact('email_errors');
    }
    
    public function actionSend()
    {
        /*$user = Yii::$app->user->identity;
        \api\modules\v1\modules\mobile\components\NotificationHelper::actionConfirm($user->email, $user->id);*/

    }
    
    public function actionRefreshFcmToken() {
        $device_id = Yii::$app->request->headers->get("Device_id");
        $token = Yii::$app->request->post('token');
        if($device_id === null)
            return "Fail";
        
        $fcm = UserFcmToken::find()->where('user_id = :user_id and device_id = :device_id', [':user_id' => Yii::$app->user->id, ':device_id' => $device_id])->one();
        
        if($fcm === null)
        {
            $fcm = new UserFcmToken();
            $fcm->device_id = $device_id;
        }
        
        $fcm->token = $token;

        return ($fcm->save()) ? "success" : print_r($fcm->getErrors());
    }

    public function actionBuisinessList(){
        $user = Yii::$app->user->getIdentity();
        $params = Yii::$app->request->queryParams;
        //$organization = new Organization();
        $sql = "
        select distinct id as `id`,`name`,`type_id` from (
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `parent_id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `id` = " . $user->organization_id . "
        union all
        select `parent_id`,
        (select `name` from `organization` where `id` = o.`parent_id`) as `name`, 
        (select `type_id` from `organization` where `id` = o.`parent_id`) as `type_id`
        from `organization` o where id = " . $user->organization_id . "
        )tb where id is not null";
        $sql2 = "
        select count(*) from (
        select distinct id as `id`,`name`,`type_id` from (
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `parent_id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `id` = " . $user->organization_id . "
        union all
        select `parent_id`,
        (select `name` from `organization` where `id` = o.`parent_id`) as `name`, 
        (select `type_id` from `organization` where `id` = o.`parent_id`) as `type_id`
        from `organization` o where id = " . $user->organization_id . "
        )tb where id is not null)tb2";

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 4;
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => \Yii::$app->db->createCommand($sql)->sql,
            'totalCount' => \Yii::$app->db->createCommand($sql2)->queryScalar(),
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        return $dataProvider;
    }

    public function actionChangeBuisiness($id)
    {
        /*$user = Yii::$app->user->getIdentity();
        $organization = Organization::findOne(['id'=>$id]);

        $sql = "
        select distinct id as `id`,`name` from (
        select id,`name` from organization where parent_id = (select id from organization where id = " . $user->organization_id . ")
        union all
        select id,`name` from organization where parent_id = (select parent_id from organization where id = " . $user->organization_id . ")
        union all
        select id,`name` from organization where id = " . $user->organization_id . "
        union all
        select parent_id,(select `name` from organization where id = o.parent_id) as name from organization o where id = " . $user->organization_id . "
        )tb where id = " . $id;
        if(\Yii::$app->db->createCommand($sql)->queryScalar() &&
            ($user->role_id == Role::ROLE_RESTAURANT_MANAGER ||
                $user->role_id == Role::ROLE_SUPPLIER_MANAGER ||
                $user->role_id == Role::ROLE_ADMIN ||
                $user->role_id == Role::ROLE_FKEEPER_MANAGER)){
            if($organization->type_id == Organization::TYPE_RESTAURANT &&
                ($user->role_id != Role::ROLE_ADMIN &&
                    $user->role_id != Role::ROLE_FKEEPER_MANAGER)){

                $user->role_id = Role::ROLE_RESTAURANT_MANAGER;
            }
            if($organization->type_id == Organization::TYPE_SUPPLIER &&
                ($user->role_id != Role::ROLE_ADMIN &&
                    $user->role_id != Role::ROLE_FKEEPER_MANAGER)){
                $user->role_id = Role::ROLE_SUPPLIER_MANAGER;
            }
            $user->organization_id = $id;
            $user->save();
            return compact('organization');
        }
        if(in_array($user->role_id, Role::getFranchiseeEditorRoles())){
            $user->organization_id = $id;
            $user->save();
            return compact('organization');
        }
        throw new BadRequestException;
    */
        return (new UserWebApi())->setOrganization(['organization_id' => $id]);
    }
}
