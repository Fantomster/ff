<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use frontend\controllers\Controller;
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
     *  index
     */

    public function actionIndex() {
        return $this->render('/site/underConstruction');
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
                    $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
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
                        $units=1;
                    }
                    if (is_int($units)==false) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Кратность]</strong> товара в неверном формате<br>(только целое число)'];
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
                        $organization->save();
                        $user->setOrganization($organization->id)->save();
                        $get_supp_org_id = $organization->id;
                        /**
                         *
                         * Отправка почты
                         * 
                         * */
                        $currentUser->sendInviteToVendor($user); //TODO: не работает отправка почты
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
        $load_data = ArrayHelper::getColumn(Category::find()->where(['in', 'id', \common\models\RelationCategory::find()->
                                    select('category_id')->
                                    where(['rest_org_id' => $currentUser->organization_id,
                                        'supp_org_id' => $supplier_org_id])])->all(), 'id');
        if (Yii::$app->request->isAjax) {
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
        return $this->renderAjax('suppliers/_viewSupplier', compact('organization', 'supplier_org_id', 'currentUser', 'load_data'));
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
        return $this->render('/site/underConstruction');
    }
    
    public function actionTutorial() {
        return $this->render('/site/underConstruction');
    }
    
    public function actionEvents() {
        return $this->render('/site/underConstruction');
    }
}
