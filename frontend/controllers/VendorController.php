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
 * Controller for supplier
 */
class VendorController extends Controller {

    private $currentUser;

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
                'only' => ['settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user'],
                'rules' => [
                    [
                        'actions' => ['settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user'],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
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
     *  Main settings page
     */

    public function actionSettings() {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new UserSearch();
        $params = Yii::$app->request->getQueryParams();
        $this->loadCurrentUser();
        $params['UserSearch']['organization_id'] = $this->currentUser->organization_id;
        $dataProvider = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('settings', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('settings', compact('searchModel', 'dataProvider'));
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
	 public function actionMycatalogs()
    {
        $relation_supp_rest = new RelationSuppRest;
        return $this->render("mycatalogs", compact("relation_supp_rest"));
    }
    public function actionChangestatus()
    {
	    if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $RelationSuppRest = new RelationSuppRest;
            
            $id = \Yii::$app->request->post('id');
            $status = \Yii::$app->request->post('status');
            $status==1?$st=0:$st=1;
	        $RelationSuppRest = RelationSuppRest::findOne(['id' => $id]);    
	        $RelationSuppRest->status = $st;
			$RelationSuppRest->update();

            $result = ['success' => true, 'status'=>$st];
            return $result;
            exit;
        }
    }
    public function actionMycatalogdelcatalog()
    {
	    if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $Catalog = new Catalog;
            $RelationSuppRest = new RelationSuppRest;
            
            $id = \Yii::$app->request->post('id');
            $result = ['success' => true, 'status'=>$id];
            return $result;
            exit;
        }
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
