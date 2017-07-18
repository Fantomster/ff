<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use api\modules\v1\modules\mobile\models\User;
use backend\modules\api\v1\resources\User as UserResource;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use common\models\forms\LoginForm;
use common\models\Profile;
use common\models\Organization;
use common\models\Role;
use common\models\UserToken;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\models\UserFcmToken;


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

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'only' => ['auth','complete-registration', 'refresh-fcm-token', 'send'],
            'authMethods' => [
                [
                    'class' => HttpBasicAuth::className(),
                    'auth' => function ($username, $password) {
            
                        $model = new LoginForm();
                        $model->email = $username;
                        $model->password = $password;
                        $model->validate();
                        return ($model->validate()) ? $model->getUser() : null;
                    }
                ],
                HttpBearerAuth::className(),
                QueryParamAuth::className()
            ]
        ];
                
        $behaviors['contentNegotiator'] = [
        'class' => ContentNegotiator::className(),
        'formats' => [
            'application/json' => Response::FORMAT_JSON
        ]

        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = UserResource::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }

    public function actionAuth() {
        
        $user = User::findOne(Yii::$app->user->id);
        $profile = $user->profile;
        $organization = $user->organization;
        return compact("user","profile","organization");
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
            }
            // set email and delete token
            $email = $newEmail ? : $user->email;
            $userToken->delete();
        }

        return ($success) ? compact("user","profile","organization") : ['error' => Yii::t('user','Invalid PIN')];
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
        $user = Yii::$app->user->identity;
        \api\modules\v1\modules\mobile\components\NotificationHelper::actionConfirm($user->email, $user->id);
    }
    
    public function actionRefreshFcmToken() {
        $device_id = Yii::$app->request->post('device_id');
        $token = Yii::$app->request->post('token');
        
        $fcm = UserFcmToken::find('user_id = :user_id and device_id = :device_id', [':user_id' => Yii::$app->user->id, ':device_id' => $device_id])->one();
        
        if($fcm === null)
        {
            $fcm = new UserFcmToken();
            $fcm->device_id = $device_id;
        }
        
        $fcm->token = $token;

        return ($fcm->save()) ? "success" : print_r($fcm->getErrors());
    }
}
