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
use amnah\yii2\user\models\UserToken;


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
            'only' => ['auth'],
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
        return User::findOne(Yii::$app->user->id);
    }
    
    public function actionRegister() {
        
        $user = new User(["scenario" => "register"]);
        $profile = new Profile (["scenario" => "register"]);
        $organization = new Organization (["scenario" => "register"]);

        //$user->setScenario("register");
        // load post data
        $post = Yii::$app->request->post();
        //var_dump($post['user']);
        if ($user->load($post, 'user')) {
            // ensure profile data gets loaded
            
            $profile->load($post, 'profile');

            // load organization data
            $organization->load($post,'organization');

            // validate for normal request
            if ($user->validate() && $profile->validate() && $organization->validate()) {

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
                return ['succcess' => 1];
                
            }
        }
        $user_errors = $user->getErrors();
        $profile_errors = $profile->getErrors();
        $organization_errors = $organization->getErrors();
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
            if (!$numSent = $user->sendEmailConfirmation($userToken)) {

                // handle email error
                //Yii::$app->session->setFlash("Email-error", "Failed to send email");
            }
        } else {
            Yii::$app->user->login($user, $this->module->loginDuration);
        }
    }
    
}
