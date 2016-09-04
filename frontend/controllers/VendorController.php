<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use yii\web\Controller;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\search\UserSearch;
use common\models\RelationSuppRest;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use yii\web\Response;
use common\components\AccessRule;
use yii\filters\AccessControl;

/**
 * Controller for supplier
 */
class VendorController extends Controller {

    private $currentUser;
	
	public $layout = "main-vendor";
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
                        // Allow suppliers managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                        ],
                    ],
                    [
                        'actions' => ['index','catalog'],
                        'allow' => true,
                        // Allow suppliers managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                        ],
                    ],
                ],
               /* 'denyCallback' => function($rule, $action) {
                    throw new HttpException(404 ,'Не здесь ничего такого, проходите, гражданин');
                }*/
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
	 public function actionCatalogs()
    {
	    
        $relation_supp_rest = new RelationSuppRest;
        return $this->render("catalogs", compact("relation_supp_rest"));
    }
     public function actionBasecatalog($id)
    {
	   $currentCatalog = $id;
	   $currentUser = User::findIdentity(Yii::$app->user->id);
	   $searchModel = new CatalogBaseGoods;
	   $searchModel2 = new RelationSuppRest;
	   $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$id);
	   $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams,$currentUser);
       return $this->render('catalogs/basecatalog', compact('searchModel', 'dataProvider','searchModel2','dataProvider2','currentCatalog'));
    }
    
    
    public function actionChangestatus()
    {
	    if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $Catalog = new Catalog;
            
            $id = \Yii::$app->request->post('id');
            $status = \Yii::$app->request->post('status');
            $status==1?$status=0:$status=1;
	        $Catalog = Catalog::findOne(['id' => $id]);    
	        $Catalog->status = $status;
			$Catalog->update();

            $result = ['success' => true, 'status'=>$status];
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
			
			$RelationSuppRest = RelationSuppRest::updateAll(['cat_id' => null],['cat_id' => $cat_id]);
            
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
            return $this->renderAjax('catalogs/_setting', ['message' => $message]);
            }
        }
        return $this->renderAjax('catalogs/_setting', compact("relation_supp_rest", "category", "relationCategory"));
    }
    public function actionChangecatalogprop()
    {
	   if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $CatalogBaseGoods = new CatalogBaseGoods;
            
            $id = \Yii::$app->request->post('id');
            $state = \Yii::$app->request->post('state');
            $elem = \Yii::$app->request->post('elem');
            if($elem=='market'){
	          	if($state=='true'){
		         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);    
				 $CatalogBaseGoods->market_place = CatalogBaseGoods::MARKETPLACE_ON;
				 $CatalogBaseGoods->update(); 	
				 
				 $result = ['success' => true, 'status'=>'update market'];
				 return $result;
	          	}else{
		         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);    
				 $CatalogBaseGoods->market_place = CatalogBaseGoods::MARKETPLACE_OFF;
				 $CatalogBaseGoods->update();  
				 
				 $result = ['success' => true, 'status'=>'no update market'];
				 return $result;	
	          	}
            }
            if($elem=='status'){
	            if($state=='true'){
		            
		         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);    
				 $CatalogBaseGoods->status = 1;
				 $CatalogBaseGoods->update();  
				 
				 $result = ['success' => true, 'status'=>'update status'];
				 return $result;
				  	
	          	}else{
		          	
		         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);    
				 $CatalogBaseGoods->status = 0;
				 $CatalogBaseGoods->update(); 	
				 
				 $result = ['success' => true, 'status'=>'no update status'];
				 return $result;
				 
	          	}
            }
        } 
    }
    public function actionChangesetcatalog()
    {
	   if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $relation_supp_rest = new RelationSuppRest;
            $curCat = \Yii::$app->request->post('curCat'); //rest_org_id
            $id = \Yii::$app->request->post('id'); //rest_org_id
            $state = \Yii::$app->request->post('state'); //true/false
	          	if($state=='true'){
		         $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $id,'sup_org_id'=>$currentUser->organization_id]);    
				 $relation_supp_rest->cat_id = $curCat;
				 $relation_supp_rest->status = 1;
				 $relation_supp_rest->update(); 	
				 
				 $result = ['success' => true, 'status'=>'ресторан '.$id.' назначен каталог '.$curCat];
				 return $result;
	          	}else{
		         $cat_id ='NULL';
		         $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $id,'sup_org_id'=>$currentUser->organization_id]);    
				 $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
				 $relation_supp_rest->status = 0;
				 $relation_supp_rest->update();  
				 
				 $result = ['success' => true, 'status'=>'unset catalog'];
				 return $result;	
	          	}
        } 
    }
    public function actionChangecatalogstatus()
    {
	   if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $Catalog = new Catalog;
            
            $id = \Yii::$app->request->post('id');
            $state = \Yii::$app->request->post('state');
	            if($state=='true'){
		            
		         $Catalog = Catalog::findOne(['id' => $id]);    
				 $Catalog->status = 1;
				 $Catalog->update();  
				 
				 $result = ['success' => true, 'status'=>'update status'];
				 return $result;
				  	
	          	}else{
		          	
		         $Catalog = Catalog::findOne(['id' => $id]);    
				 $Catalog->status = 0;
				 $Catalog->update(); 	
				 
				 $result = ['success' => true, 'status'=>'no update status'];
				 return $result;
				 
	          	}
        } 
    }
    public function actionCreateCatalog()
    {
	    $relation_supp_rest = new RelationSuppRest;
	    
	    if (Yii::$app->request->isAjax) {
		    $i =true;
            if ($i) {
	        //$post = Yii::$app->request->post();
			$message = 'Сохранено!';
            return $this->renderAjax('catalogs/_success', ['message' => $message]);
            }
        }
        return $this->renderAjax('catalogs/_create', compact('relation_supp_rest'));
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
