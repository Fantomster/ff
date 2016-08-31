<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\User;
use common\models\Profile;
use common\models\search\UserSearch;
use common\models\RelationSuppRest;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use yii\web\Response;

/**
 * Controller for supplier
 */
class VendorController extends Controller {

    private $currentUser;

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
     public function actionBasecatalog($id)
    {
	   $searchModel = new CatalogBaseGoods;
	   $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$id);
       return $this->render('mycatalogs/basecatalog', compact('searchModel', 'dataProvider'));
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
            
            $cat_id = \Yii::$app->request->post('id');
            
            $Catalog = Catalog::find()->where(['id'=>$cat_id,'type'=>2])->one();
			$Catalog->delete();
			
			$CatalogGoods = CatalogGoods::deleteAll(['cat_id' => $cat_id]);
			
			$RelationSuppRest = RelationSuppRest::updateAll(['cat_id' => null,'status' =>0],['cat_id' => $cat_id]);
            
            $result = ['success' => true];
            return $result;
            exit;
        }
    }
    public function actionSettingbasecatalog()
    {
	    $relation_supp_rest = new RelationSuppRest;
	    $relationCategory = new RelationCategory;
	    $category = new Category;
	    if (Yii::$app->request->isAjax) {
		    $i =true;
            if ($i) {
	        //$post = Yii::$app->request->post();
			$message = 'Сохранено!';
            return $this->renderAjax('mycatalogs/_setting', ['message' => $message]);
            }
        }
        return $this->renderAjax('mycatalogs/_setting', compact("relation_supp_rest", "category", "relationCategory"));
    }
    public function actionCreateCatalog()
    {
	    $relation_supp_rest = new RelationSuppRest;
	    
	    if (Yii::$app->request->isAjax) {
		    $i =true;
            if ($i) {
	        //$post = Yii::$app->request->post();
			$message = 'Сохранено!';
            return $this->renderAjax('mycatalogs/_success', ['message' => $message]);
            }
        }
        return $this->renderAjax('mycatalogs/_create', compact('relation_supp_rest'));
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
        $this->currentUser = User::findIdentity(Yii::$app->user->id);
    }

}
