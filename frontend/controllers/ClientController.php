<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use yii\web\Controller;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\search\UserSearch;
use common\components\AccessRule;
use yii\filters\AccessControl;

/**
 *  Controller for restaurant 
 */
class ClientController extends Controller {

    private $currentUser;

    public $layout = "main-client";

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index' ,'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user'],
                'rules' => [
                    [
                        'actions' => ['settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user'],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                        ],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                        ],
                    ],
                ],
                'denyCallback' => function($rule, $action) {
                    throw new HttpException(404 ,'Не здесь ничего такого, проходите, гражданин');
                }
            ],
        ];
    }
    
    /*
     *  index
     */
    public function actionIndex() {
        return $this->render('index');
    }

    /*
     *  Main settings page
     */

    public function actionSettings() {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new UserSearch();
        $params = Yii::$app->request->getQueryParams();
        $this->loadCurrentUser();
        $test = Yii::$app->user->identity;
        $params['UserSearch']['organization_id'] = $this->currentUser->organization_id;
        $dataProvider = $searchModel->search($params);
        $organization = $this->currentUser->organization;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('settings', compact('searchModel', 'dataProvider', 'organization'));
        } else {
            return $this->render('settings', compact('searchModel', 'dataProvider', 'organization'));
        }
    }

    /*
     *  User validate
     */

    public function actionAjaxValidateUser() {
        $user = new User();
        $profile = new Profile();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return json_encode(\yii\widgets\ActiveForm::validateMultiple([$user, $profile]));
                }
            }
        }
    }

    /*
     *  User create
     */

    public function actionAjaxCreateUser() {
        $user = new User(['scenario' => 'manageNew']);
        $profile = new Profile();
        $this->loadCurrentUser();
        $organizationType = $this->currentUser->organization->type_id;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->setRegisterAttributes($user->role_id)->save();
                    $profile->setUser($user->id)->save();
                    $user->setOrganization($this->currentUser->organization_id)->save();
                    $this->currentUser->sendEmployeeConfirmation($user);

                    $message = 'Пользователь добавлен!';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType'));
    }

    /*
     *  User update
     */

    public function actionAjaxUpdateUser($id) {
        $user = User::findIdentity($id);
        $user->setScenario("manage");
        $profile = $user->profile;
        $organizationType = $user->organization->type_id;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->save();
                    $profile->save();

                    $message = 'Пользователь обновлен!';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType'));
    }

    /*
     *  User delete (not actual delete, just remove organization relation)
     */

    public function actionAjaxDeleteUser() {
        //
    }

    /*
     *  Load current user 
     */

    private function loadCurrentUser() {
        $this->currentUser = Yii::$app->user->identity;//User::findIdentity(Yii::$app->user->id);
    }

}
