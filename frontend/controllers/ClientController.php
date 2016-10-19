<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use frontend\controllers\Controller;
use common\models\User;
use common\models\Role;
use common\models\Order;
use common\models\Organization;
use common\models\OrganizationType;
use common\models\RelationCategory;
use common\models\Category;
use common\models\Profile;
use common\models\Catalog;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\GoodsNotes;
use common\models\search\UserSearch;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use common\models\restaurant\RestaurantChecker;
use yii\widgets\ActiveForm;

/**
 *  Controller for restaurant 
 */
class ClientController extends DefaultController {

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
                'only' => ['index', 'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'suppliers'],
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
                        'actions' => ['index', 'suppliers'],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
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
        $organization = $this->currentUser->organization;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('settings', compact('searchModel', 'dataProvider', 'organization'));
        } else {
            return $this->render('settings', compact('searchModel', 'dataProvider', 'organization'));
        }
    }

    /*
     *  Organization validate
     */

    public function actionAjaxValidateOrganization() {
        $this->loadCurrentUser();
        $organization = $this->currentUser->organization;

        if (Yii::$app->request->isAjax && $organization->load(Yii::$app->request->post())) {
            if ($organization->validate()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return json_encode(ActiveForm::validate($organization));
            }
        }
    }

    /*
     *  Organization save
     */

    public function actionAjaxUpdateOrganization() {
        $this->loadCurrentUser();
        $organization = $this->currentUser->organization;

        if (Yii::$app->request->isAjax && $organization->load(Yii::$app->request->post())) {
            if ($organization->validate()) {
                $organization->save();
            }
        }

        return $this->renderAjax('settings/_info', compact('organization'));
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

    public function actionSuppliers() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchModel = new RelationSuppRest;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_SUPPLIERS);
        $user = new User;
        $profile = new Profile;
        $relationCategory = new RelationCategory;
        $organization = new Organization;
        return $this->render("suppliers", compact("user", "organization", "relationCategory", "profile", "searchModel", "dataProvider"));
    }

    /**
     *
     * Типы callback-ов:
     * страница мои поставщики:
     * 1 Поставщик уже есть в списке контактов (лочим все кнопки)
     * 2 Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
     * 3 Поставщик еще не авторизован / добавляем каталог
     * 4 Данный email не может быть использован (лочим все кнопки)
     * 5 Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
     * 6 Поставщик авторизован, предлагаем invite
     *
     */
    public function actionChkmail() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = RestaurantChecker::checkEmail(\Yii::$app->request->post('email'));
            return $result;
        }
    }

    public function actionCreate() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $post = Yii::$app->request->Post('User');
            $check = RestaurantChecker::checkEmail($post['email']);

            if ($check['eventType'] != 5) {
                $user = User::find()->where(['email' => $post['email']])->one();
            } else {
                $user = new User();
            }
            $relationSuppRest = new RelationSuppRest;
            $relationCategory = new RelationCategory;
            $organization = new Organization;
            $profile = new Profile();

            $post = Yii::$app->request->post();
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post); //name
            $organization->type_id = OrganizationType::TYPE_SUPPLIER; //org type_id
            $relationCategory->load($post); //array category
            $currentUser = User::findIdentity(Yii::$app->user->id);

            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);

            if ($user->validate() && $profile->validate() && $organization->validate()) {

                if ($arrCatalog === Array()) {
                    $result = ['success' => false, 'message' => 'Каталог пустой!'];
                    return $result;
                    exit;
                }
                $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
                foreach ($arrCatalog as $arrCatalogs) {
                    $product = trim($arrCatalogs['dataItem']['product']);
                    $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                    $units = (int)htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                    $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                    if (empty($article)) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Артикул]</strong> не указан'];
                        return $result;
                        exit;
                    }
                    if (empty($product)) {
                        $result = ['success' => false, 'message' => 'Ошибка: Пустое поле <strong>[Продукт]</strong>!'];
                        return $result;
                        exit;
                    }
                    if (empty($price)) {
                        $result = ['success' => false, 'message' => 'Ошибка: Пустое поле <strong>[Цена]</strong>!'];
                        return $result;
                        exit;
                    }
                    $price = str_replace(',', '.', $price);
                    if (!preg_match($numberPattern, $price)) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Цена]</strong> в неверном формате!'];
                        return $result;
                        exit;
                    }
                    if (empty($units)) {
                        $units=(int)1;
                    }
                    if (is_int($units)==false) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Кратность]</strong> товара в неверном формате<br>(только целое число)'.$units];
                        return $result;
                        exit;
                    }
                    if ($units<1) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Кратность]</strong> товара доолжно быть целым, положительным числом'];
                        return $result;
                        exit;
                    }
                }
                $email = $user->email;
                $fio = $profile->full_name;
                $org = $organization->name;
                $categorys = $relationCategory['category_id'];

                if ($check['eventType'] == 1) {
                    return $check;
                }
                if ($check['eventType'] == 2) {
                    return $check;
                }
                if ($check['eventType'] == 4) {
                    return $check;
                }
                if ($check['eventType'] == 6) {
                    return $check;
                }
                if ($check['eventType'] == 3 || $check['eventType'] == 5) {

                    if ($check['eventType'] == 5) {
                        /**
                         *
                         * Создаем нового поставщика и организацию
                         *    
                         * */
                        $user->setRegisterAttributes(Role::getManagerRole($organization->type_id))->save();
                        $profile->setUser($user->id)->save();
                        $organization->email = $user->email;
                        $organization->save();
                        $user->setOrganization($organization->id)->save();
                        $get_supp_org_id = $organization->id;
                        /**
                         *
                         * Отправка почты
                         * 
                         * */
                        $currentUser->sendInviteToVendor($user);
                    } else {
                        //Поставщик уже есть, но тот еще не авторизовался, забираем его org_id
                        $get_supp_org_id = $check['org_id'];
                    }
                    /**
                     *
                     * 1) Делаем связь категорий поставщика
                     * 
                     * */
                    foreach ($categorys as $arrCategorys) {
                        $sql = "insert into " . RelationCategory::tableName() . "(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                    /**
                     *
                     * 2) Создаем базовый и каталог для ресторана
                     *    
                     * */
                    if ($check['eventType'] == 5) {
                        $sql = "insert into " . Catalog::tableName() . "(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($get_supp_org_id,'Главный каталог'," . Catalog::BASE_CATALOG . ",NOW())";
                        \Yii::$app->db->createCommand($sql)->execute();
                        $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
                    } else {
                        $lastInsert_base_cat_id = RestaurantChecker::getBaseCatalog($get_supp_org_id);
                        $lastInsert_base_cat_id = $lastInsert_base_cat_id['id'];
                    }
                    $sql = "insert into " . Catalog::tableName() . "(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($get_supp_org_id,'" . Organization::getOrganization($currentUser->organization_id)->name . "'," . Catalog::CATALOG . ",NOW())";
                    \Yii::$app->db->createCommand($sql)->execute();
                    $lastInsert_cat_id = Yii::$app->db->getLastInsertID();

                    /**
                     *
                     * 3 и 4) Создаем каталог базовый и его продукты, создаем новый каталог для ресторана и забиваем продукты на основе базового каталога
                     *    
                     * */
                    foreach ($arrCatalog as $arrCatalogs) {
                        $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                        $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                        $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                        if (empty($units)) {
                            $units=1;
                        }
                        $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                        $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                        $price = str_replace(',', '.', $price);
                        if (substr($price, -3, 1) == '.') {
                            $price = explode('.', $price);
                            $last = array_pop($price);
                            $price = join($price, '') . '.' . $last;
                        } else {
                            $price = str_replace('.', '', $price);
                        }
                        $sql = "insert into " . CatalogBaseGoods::tableName() . "(
				      `cat_id`,`category_id`,`supp_org_id`,`article`,`product`,`units`,`price`,`status`,`market_place`,`deleted`,`created_at`) VALUES (
				      $lastInsert_base_cat_id,0,'$get_supp_org_id','$article','$product','$units','$price',1,0,0,NOW())";
                        \Yii::$app->db->createCommand($sql)->execute();
                        $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();

                        $sql = "insert into " . CatalogGoods::tableName() . "(
				      `cat_id`,`base_goods_id`,`price`,`discount`,`created_at`) VALUES (
				      $lastInsert_cat_id, $lastInsert_base_goods_id, '$price', 0,NOW())";
                        $lastInsert_goods_id = Yii::$app->db->getLastInsertID();
                        \Yii::$app->db->createCommand($sql)->execute();

                        if (!empty(trim($note))) {
                            $sql = "insert into " . GoodsNotes::tableName() . "(
				      `rest_org_id`,`catalog_goods_id`,`note`,`created_at`) VALUES (
				      $currentUser->organization_id, $lastInsert_goods_id, '$note',NOW())";
                            \Yii::$app->db->createCommand($sql)->execute();
                        }
                    }

                    /**
                     *  
                     * 5) Связь ресторана и поставщика
                     *     
                     * */
                    $relationSuppRest->rest_org_id = $currentUser->organization_id;
                    $relationSuppRest->supp_org_id = $get_supp_org_id;
                    $relationSuppRest->cat_id = $lastInsert_cat_id;
                    $relationSuppRest->status = RelationSuppRest::CATALOG_STATUS_ON;
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    $relationSuppRest->save();
                    if ($check['eventType'] == 5) {
                        $result = ['success' => true, 'message' => 'Поставщик <b>' . $fio . '</b> и каталог добавлен! Инструкция по авторизации была отправлена на почту <strong>' . $email . '</strong>'];
                        return $result;
                    } else {
                        $result = ['success' => true, 'message' => 'Каталог добавлен! приглашение было отправлено на почту  <strong>' . $email . '</strong>'];
                        return $result;
                    }
                } else {
                    $result = ['success' => false, 'message' => 'err: User уже есть в базе! Банить юзера за то, что вылезла подобная ошибка))!'];
                    return $result;
                    exit;
                }
            } else {
                $result = ['success' => false, 'message' => 'Валидация не пройдена!!!'];
                return $result;
                exit;
            }
        } else {
            $result = ['success' => false, 'message' => 'err: форма передана не ajax-ом!'];
            return $result;
            exit;
        }
    }

    public function actionInvite() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $post = Yii::$app->request->Post('User');
            $check = RestaurantChecker::checkEmail($post['email']);
            if ($check['eventType'] != 5) {
                $user = User::find()->where(['email' => $post['email']])->one();
            } else {
                $user = new User();
            }
            $relationCategory = new RelationCategory;
            $organization = new Organization;
            $profile = new Profile();

            $relationSuppRest = new RelationSuppRest;

            $post = Yii::$app->request->post();
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post); //name
            $organization->type_id = OrganizationType::TYPE_SUPPLIER; //org type_id
            $relationCategory->load($post); //array category
            $currentUser = User::findIdentity(Yii::$app->user->id);

            if ($user->validate() && $profile->validate() && $organization->validate()) {

                if ($check['eventType'] == 6) {

                    $email = $user->email;
                    $fio = $profile->full_name;
                    $org = $organization->name;
                    $categorys = $relationCategory['category_id'];
                    $get_supp_org_id = $check['org_id'];

                    $sql = "insert into " . RelationSuppRest::tableName() . "(`rest_org_id`,`supp_org_id`,`created_at`) VALUES ($currentUser->organization_id,$get_supp_org_id,NOW())";
                    \Yii::$app->db->createCommand($sql)->execute();

                    foreach ($categorys as $arrCategorys) {
                        $sql = "insert into " . RelationCategory::tableName() . "(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
                        \Yii::$app->db->createCommand($sql)->execute();
                    }
                    $result = ['success' => true, 'message' => 'Приглашение отправлено!'];
                    return $result;
                    exit;
                }
            } else {
                $result = ['success' => true, 'message' => 'Валидация не пройдена!'];
                return $result;
                exit;
            }
        }
    }

    public function actionViewSupplier($id) {
        $supplier_org_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $organization = Organization::find()->where(['id' => $supplier_org_id])->one();
        $user = User::find()->where(['email' => $organization->email])->one();
        $load_data = ArrayHelper::getColumn(Category::find()->where(['in', 'id', \common\models\RelationCategory::find()->
                                    select('category_id')->
                                    where(['rest_org_id' => $currentUser->organization_id,
                                        'supp_org_id' => $supplier_org_id])])->all(), 'id');
        if (Yii::$app->request->isAjax) {
            if(!empty($user)){
                $post = Yii::$app->request->post();
                if($user->status==0 && $post){ 
                 $organization->load($post);
                 if($organization->validate()){
                    $organization->save();
                        if($user->email != $organization->email){
                        $user->email = $organization->email;  
                        $user->save();
                        $currentUser->sendInviteToVendor($user);
                        }else{
                            if(Yii::$app->request->post('resend_email')==1){
                                $currentUser->sendInviteToVendor($user);    
                            }  
                        } 
                        
                    }else{
                    $message = 'Не верно заполнена форма!';
                    return $this->renderAjax('suppliers/_success', ['message' => $message]);    
                    }
                }
            }
            $categorys = Yii::$app->request->post('relationCategory');
            if ($categorys) {
                $sql = "DELETE FROM relation_category WHERE rest_org_id=$currentUser->organization_id AND supp_org_id=$supplier_org_id";
                \Yii::$app->db->createCommand($sql)->execute();

                foreach ($categorys as $arrCategorys) {
                    $sql = "insert into relation_category (`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ($arrCategorys,$currentUser->organization_id,$supplier_org_id,NOW())";
                    \Yii::$app->db->createCommand($sql)->execute();
                }

                $message = 'Сохранено';
                return $this->renderAjax('suppliers/_success', ['message' => $message]);
            }
        }
        return $this->renderAjax('suppliers/_viewSupplier', compact('organization', 'supplier_org_id', 'currentUser', 'load_data','user'));
    }

    public function actionViewCatalog($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::BASE_CATALOG) {
            $searchModel = new CatalogBaseGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id, NULL);
            return $this->renderAjax('suppliers/_viewBaseCatalog', compact('searchModel', 'dataProvider', 'cat_id'));
        }
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::CATALOG) {
            $searchModel = new CatalogGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);
            return $this->renderAjax('suppliers/_viewCatalog', compact('searchModel', 'dataProvider', 'cat_id'));
        }
    }

    public function actionMessages() {
        return $this->render('/site/underConstruction');
    }
    
    public function actionAnalytics() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        
        $header_info_zakaz   = \common\models\Order::find()->
                where(['client_id'=>$currentUser->organization_id])->count();
        $header_info_suppliers = \common\models\RelationSuppRest::find()->
                where(['rest_org_id'=>$currentUser->organization_id,'invite'=>RelationSuppRest::INVITE_ON])->count();
        $header_info_purchases= \common\models\Order::find()->
                where(['client_id'=>$currentUser->organization_id,'status'=>\common\models\Order::STATUS_DONE])->count();
        $header_info_items = \common\models\OrderContent::find()->select('sum(quantity) as quantity')->
                where(['in','order_id',\common\models\Order::find()->select('id')->where(['client_id'=>$currentUser->organization_id,'status'=>\common\models\Order::STATUS_DONE])])->one()->quantity;
        
        $filter_get_supplier = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                where(['in', 'id', \common\models\RelationSuppRest::find()->
                    select('supp_org_id')->
                        where(['rest_org_id'=>$currentUser->organization_id,'invite'=>'1'])])->all(),'id','name');
        $filter_get_employee = yii\helpers\ArrayHelper::map(\common\models\Profile::find()->
                where(['in', 'user_id', \common\models\User::find()->
                    select('id')->
                        where(['organization_id'=>$currentUser->organization_id])])->all(),'user_id','full_name');
        $filter_status="";
        $filter_from_date = date("d-m-Y", strtotime(" -2 months"));
        $filter_to_date = date("d-m-Y");
        $where = "";
        //pieChart
        function hex(){
        $hex = '#';
        foreach(array('r', 'g', 'b') as $color){
            //случайное число в диапазоне 0 и 255.
            $val = mt_rand(0, 255);
            //преобразуем число в Hex значение.
            $dechex = dechex($val);
            //с 0, если длина меньше 2
            if(strlen($dechex) < 2){
                $dechex = "0" . $dechex;
            }
            //объединяем
            $hex .= $dechex;
        }
        return $hex;
        } 
        if (Yii::$app->request->isAjax) {
                $filter_status=trim(\Yii::$app->request->get('filter_status'));
                $filter_supplier=trim(\Yii::$app->request->get('filter_supplier'));
                $filter_employee=trim(\Yii::$app->request->get('filter_employee'));
                $filter_from_date=trim(\Yii::$app->request->get('filter_from_date'));
                $filter_to_date=trim(\Yii::$app->request->get('filter_to_date'));
                
                empty($filter_status)?"":$where .= " and status='" . $filter_status . "'"; 
                empty($filter_supplier)?"":$where .= " and vendor_id='" . $filter_supplier . "'";
                empty($filter_employee)?"":$where .= " and created_by_id='" . $filter_employee . "'";
                        
        }
        
        $area_chart = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
                (select sum(total_price) FROM `order` 
                where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at and 
                client_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and ("
                        . "DATE(created_at) between '" . 
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . 
                        date('Y-m-d', strtotime($filter_to_date)) . "') " .
                        $where . 
                    ") AS `total_price`  
                FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS `created_at` 
                FROM `order` where 
                client_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and("
                        . "DATE(created_at) between '" . 
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . 
                        date('Y-m-d', strtotime($filter_to_date)) . "') " . $where . ")`tb`")->queryAll();
                $arr_create_at =[];
                $arr_price =[];
                if(count($area_chart)==1){
                array_push($arr_create_at, 0);  
                array_push($arr_price, 0);
                }
                
                foreach($area_chart as $area_charts){
                    array_push($arr_create_at, Yii::$app->formatter->asDatetime($area_charts['created_at'], "php:j M Y"));    
                    array_push($arr_price, $area_charts['total_price']); 
                   
                }
        /*
         * 
         * PIE CHART Аналитика по поставщикам
         * 
         */
         $vendors_total_price_sql = Yii::$app->db->createCommand("
            SELECT vendor_id,sum(total_price) as total_price FROM `order` WHERE  
                (DATE(created_at) between '" . 
                date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "') " .
                $where .
                " and client_id = " . $currentUser->organization_id . 
                " and status<>" . Order::STATUS_FORMING . " group by vendor_id")->queryAll();
        $vendors_total_price =[];
                foreach($vendors_total_price_sql as $vendors_total_price_sql_arr){
                    $arr = array(
                    'value' => $vendors_total_price_sql_arr['total_price'],
                    'label' => \common\models\Organization::find()->where(['id'=>$vendors_total_price_sql_arr['vendor_id']])->one()->name,
                    'color' => hex()
                    );
                    array_push($vendors_total_price, $arr);
                } 
        $vendors_total_price = json_encode($vendors_total_price);      
         /*
          * 
          * PIE CHART Аналитика по поставщикам END
          * 
          */
        
          /*
           * 
           * GridView Аналитика ТОП продуктов
           * 
           */
        $query = Yii::$app->db->createCommand("
            SELECT sum(price*quantity) as price,sum(quantity) as quantity, product_id FROM order_content WHERE order_id in (
                SELECT id from `order` where 
                (DATE(created_at) between '" . 
                date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "')" .
                "and status<>" . Order::STATUS_FORMING . " and client_id = " . $currentUser->organization_id . 
                $where . 
                ") group by product_id order by sum(price*quantity) desc");
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'pagination' => [
                'pageSize' => 7,
            ]
        ]);
          /*
           * 
           * GridView Аналитика ТОП продуктов END
           * 
           */
        
           /*
           * 
           * BarChart заказы по поставщикам
           * 
           */
        $chart_bar_value =[];
        $chart_bar_label =[];
        foreach($vendors_total_price_sql as $vendors_bar_total_price_sql_arr){
                    $arr = array($vendors_bar_total_price_sql_arr['total_price']);
                    array_push($chart_bar_value, $arr);
                    $arr = array(\common\models\Organization::find()->where(['id'=>$vendors_bar_total_price_sql_arr['vendor_id']])->one()->name);
                    array_push($chart_bar_label, $arr);
                } 
        $chart_bar_value = json_encode($chart_bar_value);
        $chart_bar_label = json_encode($chart_bar_label);
        /*
           * 
           * BarChart заказы по поставщикам END
           * 
           */
        return $this->render('analytics/index',compact(
                'header_info_zakaz',
                'header_info_suppliers',
                'header_info_purchases',
                'header_info_items',
                'filter_get_supplier',
                'filter_get_employee',
                'filter_supplier',
                'filter_employee',
                'filter_status',
                'filter_from_date',
                'filter_to_date',
                'arr_create_at',
                'arr_price',
                'vendors_total_price',
                'dataProvider',
                'chart_bar_value',
                'chart_bar_label'
                ));
    }
    
    public function actionTutorial() {
        return $this->render('/site/underConstruction');
    }
    
    public function actionEvents() {
        return $this->render('/site/underConstruction');
    }
    
    /*
     *  index DASHBOARD
     */

    public function actionIndex() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $suppliers_where ="";
        /*
         * 
         * Поставщики
         * 
         */
        $sql_dataProvider = Yii::$app->db->createCommand("SELECT supp_org_id FROM `relation_supp_rest` WHERE "
                . "rest_org_id = $currentUser->organization_id and invite = " . RelationSuppRest::INVITE_ON);
        $suppliers_count = Yii::$app->db->createCommand("SELECT COUNT(*) FROM (SELECT supp_org_id FROM `relation_supp_rest` WHERE "
                . "rest_org_id = $currentUser->organization_id and invite = " . RelationSuppRest::INVITE_ON . ")`tb`")->queryScalar();
        $suppliers_dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $sql_dataProvider->sql,
            'totalCount' => $suppliers_count,
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        /*
         * 
         * Поставщики END
         * 
         */
        
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $filter_from_date = date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date = date("d-m-Y");
        //GRIDVIEW ИСТОРИЯ ЗАКАЗОВ ----->
        $query = Yii::$app->db->createCommand("SELECT id,client_id,vendor_id,created_by_id,accepted_by_id,status,total_price,created_at FROM `order` WHERE "
                . "client_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING);
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM (SELECT id,client_id,vendor_id,created_by_id,accepted_by_id,status,total_price,created_at FROM `order` WHERE "
                . "client_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . ")`tb`")->queryScalar();
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 7,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'client_id',
                    'vendor_id',
                    'created_by_id',
                    'accepted_by_id',
                    'status',
                    'total_price',
                    'created_at'
                ],
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                    ]
            ],
        ]);
        // <----- GRIDVIEW ИСТОРИЯ ЗАКАЗОВ
        return $this->render('dashboard/index',compact(
                'dataProvider',
                'suppliers_dataProvider'
                ));
    }
}
