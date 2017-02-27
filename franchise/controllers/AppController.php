<?php

namespace franchise\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
use common\models\Order;
use yii\web\Response;

/**
 * Description of AppController
 *
 * @author sharaf
 */
class AppController extends DefaultController {

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
                'only' => ['index', 'settings', 'promotion', 'users'],
                'rules' => [
                    [
                        'actions' => ['index', 'settings', 'promotion', 'users'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays desktop.
     *
     * @return mixed
     */
    public function actionIndex() {
        $params = Yii::$app->request->getQueryParams();
        $searchModel = new \franchise\models\OrderSearch();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, true);

        return $this->render('index', compact('dataProvider'));
    }
    
    /**
     * Displays general settings
     * 
     * @return mixed
     */
    public function actionSettings() {
        return $this->render('/site/under-construction');
    }
    
    /**
     * Displays franchise users list
     * 
     * @return mixed
     */
    public function actionUsers() {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new \franchise\models\UserSearch();
        //$params = Yii::$app->request->getQueryParams();
        $params['UserSearch'] = Yii::$app->request->post("UserSearch");
        $this->loadCurrentUser();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('employees', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('employees', compact('searchModel', 'dataProvider'));
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
                    return json_encode(ActiveForm::validateMultiple([$user, $profile]));
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
        $organizationType = Organization::TYPE_FRANCHISEE;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->setRegisterAttributes($user->role_id)->save();
                    $profile->setUser($user->id)->save();
                    $user->setFranchisee($this->currentFranchisee->id);
//                    $this->currentUser->sendEmployeeConfirmation($user);
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
        $user = User::find()
                ->joinWith("franchiseeUser")
                ->where([
                    'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                    'user.id' => $id
                        ])
                ->one();
        $user->setScenario("manage");
        $profile = $user->profile;
        $organizationType = Organization::TYPE_FRANCHISEE;

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
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($post && isset($post['id'])) {
                $user = $user = User::find()
                ->joinWith("franchiseeUser")
                ->where([
                    'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                    'user.id' => $post["id"],
                        ])
                ->one();
                $usersCount = count($this->currentFranchisee->franchiseeUsers);
                if ($user->id == $this->currentUser->id) {
                    $message = 'Может воздержимся от удаления себя?';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1)) {
                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save() && $user->franchiseeUser->delete()) {
                        $message = 'Пользователь удален!';
                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
                }
            }
        }
        $message = 'Не удалось удалить пользователя!';
        return $this->renderAjax('settings/_success', ['message' => $message]);
    }
    /**
     * Displays promotion
     * 
     * @return mixed
     */
    public function actionPromotion() {
        return $this->render('promotion');
    }
}
