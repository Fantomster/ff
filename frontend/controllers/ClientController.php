<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use yii\web\Controller;
use common\models\User;
use common\models\Role;
use common\models\Organization;
use common\models\OrganizationType;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Profile;
use common\models\Catalog;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\search\UserSearch;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use common\models\restaurant\RestaurantChecker;

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
                'only' => ['index' ,'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'suppliers'],
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
                        'actions' => ['index','suppliers'],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                        ],
                    ],
                ],
               /*'denyCallback' => function($rule, $action) {
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
    
    public function actionSuppliers()
    {	
	    $user = new User;
	    $profile = new Profile;
	    $relationCategory = new RelationCategory;
	    $organization = new Organization;
        return $this->render("suppliers", compact("user", "organization", "relationCategory", "profile"));
    }
	/**
	*
	*Типы callback-ов:
	* страница мои поставщики:
	* 1 Поставщик уже есть в списке контактов (лочим все кнопки)
	* 2 Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
	* 3 Поставщик еще не авторизован / добавляем каталог
	* 4 Данный email не может быть использован (лочим все кнопки)
	* 5 Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
	* 6 Поставщик авторизован, предлагаем invite
	*
	*/
    public function actionChkmail()
    {
	    if (Yii::$app->request->isAjax)
	    {   
		Yii::$app->response->format = Response::FORMAT_JSON;
		$result = RestaurantChecker::checkEmail(\Yii::$app->request->post('email'));
		return $result;			
		}
	}
    public function actionCreate()
    {
	    if (Yii::$app->request->isAjax){
		    Yii::$app->response->format = Response::FORMAT_JSON;
		     
		    $post = Yii::$app->request->Post('User');
			$check = RestaurantChecker::checkEmail($post['email']);
			
			if($check['eventType']!=5){
			$user = User::find()->where(['email'=>$post['email']])->one();    
		    }else{
			$user = new User();    
		    }
			$relationSuppRest = new RelationSuppRest;
		    $relationCategory = new RelationCategory;
			$organization = new Organization;
			$profile = new Profile();
			
		    $post = Yii::$app->request->post();
		    
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post);	//name
            $organization->type_id = OrganizationType::TYPE_SUPPLIER; //org type_id
            $relationCategory->load($post); //array category
            $currentUser = User::findIdentity(Yii::$app->user->id);
			
			$arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
			
			if ($user->validate() && $profile->validate() && $organization->validate()) {
				
				if ($arrCatalog === Array()){
				  $result = ['success'=>false,'message'=>'err: Каталог пустой!'];  
				  return $result;   
				  exit; 
			    }
				
				$email = 	$user->email;
			    $fio = 		$profile->full_name;
			    $org = 		$organization->name;
			    $categorys = $relationCategory['category_id'];
				
			    if ($check['eventType']==1){return $check;}
			    if ($check['eventType']==2){return $check;}
			    if ($check['eventType']==4){return $check;}
			    if ($check['eventType']==6){return $check;}
			    if ($check['eventType']==3 || $check['eventType']==5) { 
				        
				    if($check['eventType']==5){
					/**
				    *
					* Создаем нового поставщика и организацию
					*    
					**/	
					$user->setRegisterAttributes(Role::getManagerRole($organization->type_id))->save();
                    $profile->setUser($user->id)->save();
                    $organization->save();
                    $user->setOrganization($organization->id)->save();
                    $get_supp_org_id = $organization->id;
                    /**
				    *
					* Отправка почты
					* 
					**/
					$currentUser->sendInviteToVendor($user); //TODO: не работает отправка почты
					}else{
					//Поставщик уже есть, но тот еще не авторизовался, забираем его org_id
					$get_supp_org_id = $check['org_id'];
					}
					/**
				    *
					* 1) Делаем связь категорий поставщика
					* 
					**/
					
					foreach ( $categorys as $arrCategorys ) { 
					$sql = "insert into ".RelationCategory::tableName()."(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
				    \Yii::$app->db->createCommand($sql)->execute(); 	
				    }
				    /**
				    *
					* 2) Создаем базовый и каталог для ресторана
					*    
					**/
					
					if($check['eventType']==5){
					$sql = "insert into ".Catalog::tableName()."(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($get_supp_org_id,'default',".Catalog::BASE_CATALOG.",NOW())";
				    \Yii::$app->db->createCommand($sql)->execute(); 
				    $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
				    }else{
					$lastInsert_base_cat_id = RestaurantChecker::getBaseCatalog($get_supp_org_id);
					$lastInsert_base_cat_id=$lastInsert_base_cat_id['id'];    
				    }
				    $sql = "insert into ".Catalog::tableName()."(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($get_supp_org_id,'default',".Catalog::CATALOG.",NOW())";
				    \Yii::$app->db->createCommand($sql)->execute(); 
				    $lastInsert_cat_id = Yii::$app->db->getLastInsertID();
				    
				    /**
				    *
					* 3 и 4) Создаем каталог базовый и его продукты, создаем новый каталог для ресторана и забиваем продукты на основе базового каталога
					*    
					**/
				    
				    foreach ( $arrCatalog as $arrCatalogs ) { 
				      $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
				      $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
				      $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
				      $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
				      $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note'])); 
				             
				      $sql = "insert into ".CatalogBaseGoods::tableName()."(
				      `cat_id`,`category_id`,`article`,`product`,`units`,`price`,`status`,`market_place`,`deleted`,`created_at`) VALUES (
				      $lastInsert_base_cat_id,0,'$article','$product','$units','$price',1,0,0,NOW())";
				      \Yii::$app->db->createCommand($sql)->execute();
				      $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();
				      
				      $sql = "insert into ".CatalogGoods::tableName()."(
				      `cat_id`,`base_goods_id`,`price`,`note`,`discount`,`created_at`) VALUES (
				      $lastInsert_cat_id, $lastInsert_base_goods_id, '$price', '$note', 0,NOW())";
				      \Yii::$app->db->createCommand($sql)->execute();       
				    }
				    
				    /**
				    *
					* 5) Связь ресторана и поставщика
					*    
					**/
					
					$relationSuppRest->rest_org_id = $currentUser->organization_id;
					$relationSuppRest->supp_org_id = $get_supp_org_id;
					$relationSuppRest->cat_id = $lastInsert_cat_id;
					$relationSuppRest->save();
					if($check['eventType']==5){				    
				    $result = ['success'=>true,'message'=>'Поставщик <b>'.$fio.'</b> и каталог добавлен! Инструкция по авторизации была отправлена на почту <strong>'.$email.'</strong>']; 
				    return $result;
				    }else{
					$result = ['success'=>true,'message'=>'Каталог добавлен! Уведомление было отправлено на почту  <strong>'.$email.'</strong>']; 
				    return $result;    
				    }
				}else{
				$result = ['success'=>false,'message'=>'err: User уже есть в базе! Банить юзера за то, что вылезла подобная ошибка))!']; 
				return $result;
				exit; 
				}
			}else{
		$result = ['success'=>false,'message'=>'Валидация не пройдена!!!'];
		return $result;
		exit; 	
		}
		}else{
		$result = ['success'=>false,'message'=>'err: форма передана не ajax-ом!'];
		return $result;
		exit; 
		} 
    }
    public function actionInvite()
    {
	  	if (Yii::$app->request->isAjax){
		    Yii::$app->response->format = Response::FORMAT_JSON;
		    
		    $post = Yii::$app->request->Post('User');
		    $check = RestaurantChecker::checkEmail($post['email']);
		    if($check['eventType']!=5){
			$user = User::find()->where(['email'=>$post['email']])->one();    
		    }else{
			$user = new User();    
		    }
		    $relationCategory = new RelationCategory;
			$organization = new Organization;
			$profile = new Profile();
			
			$relationSuppRest = new RelationSuppRest;
		    
		    $post = Yii::$app->request->post();
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post);	//name
            $organization->type_id = OrganizationType::TYPE_SUPPLIER; //org type_id
            $relationCategory->load($post); //array category
            $currentUser = User::findIdentity(Yii::$app->user->id);
            
		    if ($user->validate() && $profile->validate() && $organization->validate()) {
	        
	        if($check['eventType']==6){
		        
		        
		        
		        $email = 	$user->email;
				$fio = 		$profile->full_name;
				$org = 		$organization->name;
				$categorys = $relationCategory['category_id'];
				$get_supp_org_id = $check['org_id'];
				
				$sql = "insert into ".RelationSuppRest::tableName()."(`rest_org_id`,`supp_org_id`,`created_at`) VALUES ($currentUser->organization_id,$get_supp_org_id,NOW())";
				\Yii::$app->db->createCommand($sql)->execute();
				
			    foreach ( $categorys as $arrCategorys ) { 
					$sql = "insert into ".RelationCategory::tableName()."(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
				    \Yii::$app->db->createCommand($sql)->execute(); 	
				    }
			    $result = ['success'=>true,'message'=>'Приглашение отправлено!'];
				return $result;
				exit; 
			    }
		    }else{
				$result = ['success'=>true,'message'=>'Валидация не пройдена!'];
				return $result;
				exit;     
		    }
		}  
    }

}
