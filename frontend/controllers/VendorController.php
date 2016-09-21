<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
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
use common\models\GoodsNotes;
use common\models\CatalogBaseGoods;
use yii\web\Response;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
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
	$currentUser = User::findIdentity(Yii::$app->user->id);
        if(!Catalog::find()->where(['supp_org_id'=>$currentUser->organization_id,'type'=>Catalog::BASE_CATALOG])->exists()){
        return $this->render("catalogs/createBaseCatalog", compact("Catalog")); 
        }else{
        $relation_supp_rest = new RelationSuppRest;
        return $this->render("catalogs", compact("relation_supp_rest"));
        }
    }
    public function actionSupplierStartCatalogCreate ()
    {   
        if (Yii::$app->request->isAjax){
        Yii::$app->response->format = Response::FORMAT_JSON;  
        $currentUser = User::findIdentity(Yii::$app->user->id);
        
        $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
        
        if ($arrCatalog === Array()){
            $result = ['success'=>false,'message'=>'err: Каталог пустой!'];  
            return $result;   
            exit; 
            }
        //проверка на корректность введенных данных (цена)
        $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
        foreach ( $arrCatalog as $arrCatalogs ) {
            $product = trim($arrCatalogs['dataItem']['product']);
            if(empty($product)){
            $result = ['success'=>false,'message'=>'Ошибка: Пустое поле <strong>[Продукт]</strong>!'];  
            return $result;   
            exit;    
            }
            $price = $arrCatalogs['dataItem']['price'];
            $price = str_replace(',', '.', $price);
            if(substr($price, -3, 1) == '.')
            {
                $price = explode('.', $price);
                $last = array_pop($price);
                $price = join($price, '').'.'.$last;
            }
            else
            {
                $price = str_replace('.', '', $price);
            }
            if (!preg_match($numberPattern,$price)) {
            $result = ['success'=>false,'message'=>'Ошибка: <strong>[Цена]</strong> в неверном формате!'];  
            return $result;   
            exit;    
            }
        }
        $sql = "insert into ".Catalog::tableName()."(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($currentUser->organization_id,'default',".Catalog::BASE_CATALOG.",NOW())";
	\Yii::$app->db->createCommand($sql)->execute(); 
	$lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
        
        foreach ( $arrCatalog as $arrCatalogs ) { 
            $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
            $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
            $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
            $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
            $price = str_replace(',', '.', $price);
            if(substr($price, -3, 1) == '.')
            {
                $price = explode('.', $price);
                $last = array_pop($price);
                $price = join($price, '').'.'.$last;
            }
            else
            {
                $price = str_replace('.', '', $price);
            }
            
            //$price = $price*100;//bigInt format
            
            $sql = "insert into ".CatalogBaseGoods::tableName()."(
            `cat_id`,`category_id`,`article`,`product`,`units`,`price`,`status`,`market_place`,`deleted`,`created_at`) VALUES (
            $lastInsert_base_cat_id,0,'$article','$product','$units','$price',1,0,0,NOW())";
            \Yii::$app->db->createCommand($sql)->execute();

            
            }
        $result = ['success'=>true,'message'=>'Каталог успешно создан!'];  
        return $result;   
        exit;
        }
    }
     public function actionClients()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
	$searchModel = new RelationSuppRest;
	$dataProvider = $searchModel->search(Yii::$app->request->queryParams,$currentUser,RelationSuppRest::PAGE_CLIENTS);
        return $this->render('clients', compact('searchModel', 'dataProvider'));
    }
     public function actionBasecatalog($id)
    {
           $currentCatalog = $id;
	   $currentUser = User::findIdentity(Yii::$app->user->id);
	   $searchModel = new CatalogBaseGoods;
	   $searchModel2 = new RelationSuppRest;
	   $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$id,NULL);
	   $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams,$currentUser,RelationSuppRest::PAGE_CATALOG);
      return $this->render('catalogs/basecatalog', compact('searchModel', 'dataProvider','searchModel2','dataProvider2','currentCatalog'));
    }
    public function actionCatalog($id)
    {
	   $currentCatalog = $id;
	   $currentUser = User::findIdentity(Yii::$app->user->id);
	   $searchModel = new CatalogGoods;
	   $searchModel2 = new RelationSuppRest;
	   $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$id);
	   $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams,$currentUser,RelationSuppRest::PAGE_CATALOG);
       return $this->render('catalogs/catalog', compact('searchModel', 'dataProvider','searchModel2','dataProvider2','currentCatalog'));
    } 
    public function actionImportToXls($id)
    {
        $importModel = new \common\models\upload\UploadForm();
            if (Yii::$app->request->isPost) {
                    $unique = \Yii::$app->request->post('importUnique');
                    $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile');
                    $path = $importModel->upload();
                    $currentUser = User::findIdentity(Yii::$app->user->id);
                    try{
                         $inputFileType = \PHPExcel_IOFactory::identify($path);
                         $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                         $objPHPExcel = $objReader->load($path);

                     } catch (Exception $ex) {             
                         die('Error');
                     }

                     $sheet = $objPHPExcel->getSheet(0);
                     $highestRow = $sheet->getHighestRow();
                     $highestColumn = $sheet->getHighestColumn();
                     //импорт таблицы начиная со второй строки
                     $sql_array_products = CatalogBaseGoods::find()->select($unique)->where(['cat_id'=>$id])->asArray()->all();
                     $count_array = count($sql_array_products);
                     $arr = [];
                     for ($i = 0; $i < $count_array; $i++) {
                        array_push($arr,$sql_array_products[$i][$unique]);
                        }
                     for($row=2; $row<=$highestRow; ++$row)
                     {         
                         
                         $rowData = $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row,NULL,TRUE,FALSE);
                         $row_article = htmlspecialchars(trim($rowData[0][0]));
                         $row_product = htmlspecialchars(trim($rowData[0][1]));
                         $row_units = htmlspecialchars(trim($rowData[0][2]));
                         $row_price = htmlspecialchars(trim($rowData[0][3]));
                         $row_price = floatval(preg_replace("/[^-0-9\.]/","",$row_price));
                          
                        if(!empty($row_article && $row_product && $row_units && $row_price)){
                            $unique = \Yii::$app->request->post('importUnique')=='product'?$row_product:$row_article;
                            if(!in_array($unique,$arr)){ 
                               $sql = "insert into ".CatalogBaseGoods::tableName().
                                  "(`cat_id`,`category_id`,`supp_org_id`,`article`,`product`,`units`,`price`,`status`,`created_at`) VALUES "
                                . "($id,0,$currentUser->organization_id,'{$row_article}','{$row_product}','{$row_units}','{$row_price}',".CatalogBaseGoods::STATUS_ON.",NOW())";
                              \Yii::$app->db->createCommand($sql)->execute(); 
                            }
                        }
                     }
                     unlink($path);
                     //не нашел другого способа как обновить без перезагрузки =(((
                     //Есть идея через pjax обновлять модальное окно с редиректом при успехе _success.php
                     return $this->redirect(['vendor/basecatalog','id'=>$id]);
            }
        return $this->renderAjax('catalogs/_importForm', compact('importModel'));
    }
    public function actionImportBaseCatalogFromXls()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
            if (Yii::$app->request->isPost) {
                    //$unique = \Yii::$app->request->post('importUnique');
                    $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile');
                    $path = $importModel->upload();
                    $currentUser = User::findIdentity(Yii::$app->user->id);
                    try{
                         $inputFileType = \PHPExcel_IOFactory::identify($path);
                         $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                         $objPHPExcel = $objReader->load($path);

                     } catch (Exception $ex) {             
                         die('Error');
                     }

                     $sheet = $objPHPExcel->getSheet(0);
                     $highestRow = $sheet->getHighestRow();
                     $highestColumn = $sheet->getHighestColumn();
                     //импорт таблицы начиная со второй строки
                     $sql = "insert into ".Catalog::tableName()."(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($currentUser->organization_id,'default',".Catalog::BASE_CATALOG.",NOW())";
			\Yii::$app->db->createCommand($sql)->execute(); 
			$lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
                    
                
                     for($row=2; $row<=$highestRow; ++$row)
                     {         
                         $rowData = $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row,NULL,TRUE,FALSE);
                         $row_article = htmlspecialchars(trim($rowData[0][0]));
                         $row_product = htmlspecialchars(trim($rowData[0][1]));
                         $row_units = htmlspecialchars(trim($rowData[0][2]));
                         $row_price = htmlspecialchars(trim($rowData[0][3]));
                         $row_price = floatval(preg_replace("/[^-0-9\.]/","",$row_price));
                        if(!empty($row_article && $row_product && $row_units && $row_price)){
         
                               $sql = "insert into ".CatalogBaseGoods::tableName().
                                  "(`cat_id`,`category_id`,`supp_org_id`,`article`,`product`,`units`,`price`,`status`,`created_at`) VALUES "
                                . "($lastInsert_base_cat_id,0,$currentUser->organization_id,'{$row_article}','{$row_product}','{$row_units}','{$row_price}',".CatalogBaseGoods::STATUS_ON.",NOW())";
                              \Yii::$app->db->createCommand($sql)->execute(); 

                        }
                     }
                     unlink($path);
                     //не нашел другого способа как обновить без перезагрузки =(((
                     //Есть идея через pjax обновлять модальное окно с редиректом при успехе _success.php
                     return $this->redirect(['vendor/basecatalog','id'=>$lastInsert_base_cat_id]);
            }
        return $this->renderAjax('catalogs/_importCreateBaseForm', compact('importModel'));
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
    public function actionAjaxInviteRestOrgId()
    {
	    if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $relationSuppRest = new RelationSuppRest;
            
            $id = \Yii::$app->request->post('id');
            $elem = \Yii::$app->request->post('elem');
            $state = \Yii::$app->request->post('state');
            if($elem=='restOrgId'){
	        if($state=='true'){
		    $relationSuppRest = RelationSuppRest::findOne(['rest_org_id' => $id,'supp_org_id'=>$currentUser->organization_id]);    
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    $relationSuppRest->update(); 	
				 
                    $result = ['success' => true, 'status'=>'update invite'];
                    return $result;
                    }else{
		    $relationSuppRest = RelationSuppRest::findOne(['rest_org_id' => $id,'supp_org_id'=>$currentUser->organization_id]);    
                    $relationSuppRest->invite = RelationSuppRest::INVITE_OFF;
                    $relationSuppRest->cat_id = RelationSuppRest::INVITE_OFF;
                    $relationSuppRest->update();  
				 
                    $result = ['success' => true, 'status'=>'no update invite'];
                    return $result;	
	            }
            }
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
    public function actionAjaxDeleteProduct()
    {
	    if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $product_id = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll(['deleted' => 1],['id' => $product_id]);
            
            $result = ['success' => true];
            return $result;
            exit;
        }
    }
    /*
     *  User product
     */

    public function actionAjaxUpdateProduct($id) {
        $catalogBaseGoods = CatalogBaseGoods::find()->where(['id'=>$id])->one(); 
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            
            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/","",str_replace(',', '.', $catalogBaseGoods->price));
                if ($catalogBaseGoods->validate()) {
                    
                    $catalogBaseGoods->save();

                    $message = 'Продукт обновлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('catalogs/_baseProductForm', compact('catalogBaseGoods'));
    }
 
    public function actionAjaxCreateProduct() {
        if (Yii::$app->request->isAjax) {
	    $catalogBaseGoods = new CatalogBaseGoods();
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                
                if ($catalogBaseGoods->validate()) {
		
                    $catalogBaseGoods->save();
					
                    $message = 'Продукт добавлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_baseProductForm', compact('catalogBaseGoods'));
    }
    public function actionChangecatalogprop()
    {
	   if (Yii::$app->request->isAjax) {
              
            Yii::$app->response->format = Response::FORMAT_JSON;
            $CatalogBaseGoods = new CatalogBaseGoods;
            $id = \Yii::$app->request->post('id');
            $elem = \Yii::$app->request->post('elem');

            if($elem=='market'){
		         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);  
                         if($CatalogBaseGoods->market_place==0){$set = 1;}else{$set = 0;}
                         $CatalogBaseGoods->market_place = $set;
			 $CatalogBaseGoods->update(); 	
                         
			 $result = ['success' => true, 'status'=>'update market'];
			 return $result;
	          	}
            if($elem=='status'){
                         $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]); 
                         if($CatalogBaseGoods->status==0){$set = 1;}else{$set = 0;}
			 $CatalogBaseGoods->status = $set;
			 $CatalogBaseGoods->update();  
				 
			 $result = ['success' => true, 'status'=>'update status'];
			 return $result;
			
            }
        } 
    }
    public function actionChangesetcatalog()
    {
	   if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            //$relation_supp_rest = new RelationSuppRest;
            $curCat = \Yii::$app->request->post('curCat'); //catalog
            $id = \Yii::$app->request->post('id'); //rest_org_id

                        $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $id,'supp_org_id'=>$currentUser->organization_id]);

                        if($relation_supp_rest->status==0){$set = 1;}else{$set = 0;}
                        
			$relation_supp_rest->cat_id = $relation_supp_rest->status==0 ? $curCat : Catalog::NON_CATALOG ;
			$relation_supp_rest->status = $set;
			$relation_supp_rest->update(); 	
				 
			$result = ['success' => true, 'status'=>'ресторан '.$id.' назначен каталог '.$curCat];
			return $result;
	          	}
    }
    public function actionChangecatalogstatus()
    {
	   if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            //$Catalog = new Catalog;
            $id = \Yii::$app->request->post('id');
                    $Catalog = Catalog::findOne(['id' => $id]);  
                    if($Catalog->status==0){$set = 1;}else{$set = 0;}
                    $Catalog->status = Catalog::STATUS_ON;
                    $Catalog->update();  

                    $result = ['success' => true, 'status'=>'update status'];
                    return $result;
        } 
    }
    public function actionCreateCatalog()
    {
	$relation_supp_rest = new RelationSuppRest;
	if (Yii::$app->request->isAjax) {
	
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
        $this->currentUser = Yii::$app->user->identity;
    }

    public function actionStep1(){
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $catalog = new Catalog();
            $post = Yii::$app->request->post();
            $currentUser = User::findIdentity(Yii::$app->user->id);
            if ($catalog->load($post)) {
                $catalog->supp_org_id=$currentUser->organization_id;
                $catalog->type=Catalog::CATALOG;
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id'=>$catalog->id]); 
                }else{
                 return (['success' => false,'type'=>1, 'Валидация не пройдена']);  
                 exit;
                }
            }else{
            return (['success' => false,'type'=>2, 'POST не определен']);  
            exit;
            }
        }
        $catalog = new Catalog();
        return $this->render('newcatalog/step-1',compact('catalog'));  
    }
    public function actionStep1Update($id){
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $catalog = Catalog::find()->where(['id'=>$cat_id])->one();
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $post = Yii::$app->request->post();
                if ($catalog->load($post)) {
                    if ($catalog->validate()) {
                        $catalog->save(); 
                        return (['success' => true, 'cat_id'=>$catalog->id]); 
                    }else{
                        return (['success' => false,'type'=>1, 'Валидация не пройдена']);  
                        exit;
                    }
                }
            }
        return $this->render('newcatalog/step-1',compact('catalog','cat_id','searchModel','dataProvider'));
    }
    public function actionStep1Clone($id){
        $cat_id_old = $id; //id исходного каталога
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $model=Catalog::findOne(['id' => $id]);
        $model->id = null;
        $model->name = $model->type==Catalog::BASE_CATALOG ? 'Базовый каталог '.date("Y-m-d") : $model->name.' дубликат';
        $cat_type=$model->type;   //текущий тип каталога(исходный)    
        $model->type = Catalog::CATALOG;//переопределяем тип на 2
        $model->isNewRecord = true;
        $model->save();
        
        $cat_id = $model->id;//новый каталог id
        if($cat_type==Catalog::BASE_CATALOG){
        $sql = "insert into ".CatalogGoods::tableName().
                "(`cat_id`,`base_goods_id`,`price`,`created_at`) "
                . "SELECT ".$cat_id.", id, price, NOW() from ".CatalogBaseGoods::tableName()." WHERE cat_id = $cat_id_old";
        \Yii::$app->db->createCommand($sql)->execute(); 
        }
        if($cat_type==Catalog::CATALOG){
        $sql = "insert into ".CatalogGoods::tableName().
                "(`cat_id`,`base_goods_id`,`price`,`created_at`) "
                . "SELECT ".$cat_id.", base_goods_id, price, NOW() from ".CatalogGoods::tableName()." WHERE cat_id = $cat_id_old";
        \Yii::$app->db->createCommand($sql)->execute();     
        }

        return $this->redirect(['vendor/step-1-update','id'=>$cat_id]);
    }
    public function actionStep2($id){
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
                if (Yii::$app->request->post('check')) {  
                  if(CatalogGoods::find()->where(['cat_id' => $cat_id])->exists()){
                  return (['success' => true, 'cat_id'=>$cat_id]);     
                  }else{
                  return (['success' => false,'type'=>1,'message' => 'Пустой каталог']);  
                  exit;    
                  }
                } 
                if (Yii::$app->request->post('add-product')) {   
                  if(Yii::$app->request->post('state')=='true'){
                   
                   $product_id = Yii::$app->request->post('baseProductId');
                   $catalogGoods = new CatalogGoods;
                   $catalogGoods->base_goods_id = $product_id;
                   $catalogGoods->cat_id = $cat_id;
                   $catalogGoods->price = CatalogBaseGoods::findOne(['id'=>$product_id])->price;
                   $catalogGoods->save();
                   return (['success' => true, 'Добавлен']);  
                   exit; 
                  }else{
                   $product_id = Yii::$app->request->post('baseProductId');
                   $CatalogGoods = CatalogGoods::deleteAll(['base_goods_id' => $product_id]);    
                   return (['success' => false, 'Удален']);  
                   exit;    
                  }
                     
                }
            }
        
        $baseCatalog = Catalog::findOne(['supp_org_id'=>$currentUser->organization_id,'type'=>Catalog::BASE_CATALOG]);
        $searchModel = new CatalogBaseGoods;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$baseCatalog->id);
        return $this->render('newcatalog/step-2',compact('searchModel', 'dataProvider','cat_id'));
    }
    public function actionStep3($id){
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchModel = new CatalogGoods();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$cat_id);
        
        $exportModel = new CatalogBaseGoods;
	$exportProvider = $exportModel->search(Yii::$app->request->queryParams,$cat_id,NULL);
        
        return $this->render('newcatalog/step-3',compact('searchModel', 'dataProvider','exportModel','exportProvider','cat_id'));
    }
    public function actionStep3UpdateProduct($id) {
        $catalogGoods = CatalogGoods::find()->where(['id'=>$id])->one(); 
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogGoods->load($post)) {
                if ($catalogGoods->validate()) {

                    $catalogGoods->save();

                    $message = 'Продукт обновлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_productForm', compact('catalogGoods'));
    }
    public function actionStep4($id){
        $cat_id = $id;
	$currentUser = User::findIdentity(Yii::$app->user->id);
	$searchModel = new RelationSuppRest;
	$dataProvider = $searchModel->search(Yii::$app->request->queryParams,$currentUser,RelationSuppRest::PAGE_CATALOG);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('add-client')) {  
                if(Yii::$app->request->post('state')=='true'){
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id,'supp_org_id'=>$currentUser->organization_id]);
                    $relation_supp_rest->cat_id = $cat_id;
                    $relation_supp_rest->status = 1;
                    $relation_supp_rest->update();

                    return (['success' => true, 'Подписан']); 
                    exit;
                }else{
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id,'supp_org_id'=>$currentUser->organization_id]);    
                    $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                    $relation_supp_rest->status = 0;
                    $relation_supp_rest->update(); 
                    return (['success' => true, 'Не подписан']);
                    exit;
                }
            }
        }
        return $this->render('newcatalog/step-4', compact('searchModel', 'dataProvider','currentCatalog','cat_id'));
    }
    public function actionAjaxAddClient() {
        $user = new User();
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                if ($user->validate()) {
                    //class отправки приглашения ресторану от поставщика
                    $message = 'Приглашение отправлено!';
                    return $this->renderAjax('clients/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('clients/_addClientForm', compact('user'));
    }
    public function actionAjaxSetPercent($id){
        $cat_id = $id;
        $catalogGoods = new CatalogGoods(['scenario' => 'update']); 
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $catalogGoods->cat_id = $cat_id;
            if ($catalogGoods->load($post)) {
               if ($catalogGoods->validate()) {
                $catalogGoods = CatalogGoods::updateAll(['discount_percent' => $catalogGoods->discount_percent],['cat_id' => $cat_id]);   
                //var_dump($catalogGoods);
                $message = "Сохранено!";
                return $this->renderAjax('catalogs/_success',['message' => $message]);   
               }
            }
             
        }
        return $this->renderAjax('catalogs/_setPercentCatalog', compact('catalogGoods','cat_id'));  
    }
}