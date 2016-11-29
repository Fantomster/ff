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
                'only' => ['index', 'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'suppliers', 'tutorial'],
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
                        'actions' => ['index', 'suppliers', 'tutorial'],
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
        $organization = $this->currentUser->organization;

        if ($organization->load(Yii::$app->request->get())) {
            if ($organization->validate()) {
                if ($organization->step == Organization::STEP_SET_INFO) {
                    $organization->step = Organization::STEP_ADD_VENDOR;
                    $organization->save();
                    return $this->redirect(['client/suppliers']);
                }
                $organization->save();
            }
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('settings', compact('organization'));
        } else {
            return $this->render('settings', compact('organization'));
        }
    }

    /*
     *  user list page
     */

    public function actionEmployees() {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new UserSearch();
        //$params = Yii::$app->request->getQueryParams();
        $params['UserSearch'] = Yii::$app->request->post("UserSearch");
        $this->loadCurrentUser();
        $params['UserSearch']['organization_id'] = $this->currentUser->organization_id;
        $dataProvider = $searchModel->search($params);

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
               if(count($arrCatalog)>1000){
               $result = ['success' => false, 'message' => 'Чтобы добавить больше <strong>1000</strong> позиций, пожалуйста свяжитесь с нами '
                   . '<a href="mailto://info@f-keeper.ru" target="_blank" class="text-success">info@f-keeper.ru</a>'];
               return $result;
               exit;     
                }
                foreach ($arrCatalog as $arrCatalogs) {
                    $product = trim($arrCatalogs['dataItem']['product']);
                    $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                    $units = trim($arrCatalogs['dataItem']['units']);
                    $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                    $ed = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
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
                    if (empty($units) || $units < 0) {
                        $units = 0;
                    }
                    $units = str_replace(',', '.', $units);
                    if (!empty($units) && !preg_match($numberPattern, $units)) {
                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Кратность]</strong> товара в неверном формате'];
                        return $result;
                        exit;
                    }
                    if (empty($ed)) {
                        $result = ['success' => false, 'message' => 'Ошибка: Пустое поле <strong>[Единица измерения]</strong>!'];
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
                        $currentOrganization = $currentUser->organization;
                        if ($currentOrganization->step == Organization::STEP_ADD_VENDOR) {
                            $currentOrganization->step = Organization::STEP_OK;
                            $currentOrganization->save();
                        }
                        
                    } else {
                        //Поставщик уже есть, но тот еще не авторизовался, забираем его org_id
                        $get_supp_org_id = $check['org_id'];
                    }
                    /**
                     *
                     * 1) Делаем связь категорий поставщика
                     * 
                     * */
                    if (!empty($categorys)) {
                        foreach ($categorys as $arrCategorys) {
                            $sql = "insert into " . RelationCategory::tableName() . "(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
                            \Yii::$app->db->createCommand($sql)->execute();
                        }
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
                        $article = trim($arrCatalogs['dataItem']['article']);
                        $product = trim($arrCatalogs['dataItem']['product']);
                        $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                        $units = str_replace(',', '.', $units);
                        if (substr($units, -3, 1) == '.') {
                            $units = explode('.', $units);
                            $last = array_pop($units);
                            $units = join($units, '') . '.' . $last;
                        } else {
                            $units = str_replace('.', '', $units);
                        }
                        if (empty($units) || $units < 0) {
                            $units = 'NULL';
                        }
                        $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                        $note = trim($arrCatalogs['dataItem']['note']);
                        $ed = trim($arrCatalogs['dataItem']['ed']);
                        $price = str_replace(',', '.', $price);
                        if (substr($price, -3, 1) == '.') {
                            $price = explode('.', $price);
                            $last = array_pop($price);
                            $price = join($price, '') . '.' . $last;
                        } else {
                            $price = str_replace('.', '', $price);
                        }
                        $sql = "insert into {{%catalog_base_goods}}" .
                                "(`cat_id`,`category_id`,`supp_org_id`,`article`,`product`,"
                                . "`units`,`price`,`note`,`ed`,`status`,`market_place`,`deleted`,`created_at`) VALUES ("
                                . $lastInsert_base_cat_id . ","
                                . "0,"
                                . $get_supp_org_id . ","
                                . ":article,"
                                . ":product,"
                                . ":units,"
                                . ":price,"
                                . ":note,"
                                . ":ed,"
                                . CatalogBaseGoods::STATUS_ON . ","
                                . "0,"
                                . "0,"
                                . "NOW())";
                        $command = \Yii::$app->db->createCommand($sql);
                        $command->bindParam(":article", $article, \PDO::PARAM_STR);
                        $command->bindParam(":product", $product, \PDO::PARAM_STR);
                        $command->bindParam(":units", $units);
                        $command->bindParam(":price", $price);
                        $command->bindParam(":note", $note, \PDO::PARAM_STR);
                        $command->bindParam(":ed", $ed, \PDO::PARAM_STR);
                        $command->execute();
                        $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();

                        $sql = "insert into " . CatalogGoods::tableName() . "(
				      `cat_id`,`base_goods_id`,`price`,`discount`,`created_at`) VALUES (
				      $lastInsert_cat_id, $lastInsert_base_goods_id, '$price', 0,NOW())";
                        $lastInsert_goods_id = Yii::$app->db->getLastInsertID();
                        \Yii::$app->db->createCommand($sql)->execute();

                        if (!empty($note)) {
                            $sql = "insert into " . GoodsNotes::tableName() . "(
				      `rest_org_id`,`catalog_base_goods_id`,`note`,`created_at`) VALUES (
				      $currentUser->organization_id, $lastInsert_base_goods_id, '$note',NOW())";
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
                    /**
                         *
                         * Отправка почты
                         * 
                         * */
                    $currentUser->sendInviteToVendor($user);
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
                    $supp_base_cat_id = Catalog::find()->where(['supp_org_id'=>$get_supp_org_id, 'type'=>1])->one()->id;
                    $sql = "insert into " . RelationSuppRest::tableName() . "(`rest_org_id`,`supp_org_id`,`created_at`,`invite`,`status`,`cat_id`) VALUES ($currentUser->organization_id,$get_supp_org_id,NOW(),1,1,$supp_base_cat_id)";
                    \Yii::$app->db->createCommand($sql)->execute();
                    if(!empty($categorys)){
                        foreach ($categorys as $arrCategorys) {
                            $sql = "insert into " . RelationCategory::tableName() . "(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
                            \Yii::$app->db->createCommand($sql)->execute();
                        }
                    }
                    $result = ['success' => true, 'message' => 'Приглашение отправлено!'];
                    $currentOrganization = $currentUser->organization;
                    if ($currentOrganization->step == Organization::STEP_ADD_VENDOR) {
                        $currentOrganization->step = Organization::STEP_OK;
                        $currentOrganization->save();
                    }
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
        !empty($user) ? $user->status == 0 ? $userStatus = 1 : $userStatus = 0 : $userStatus = '';
        $load_data = ArrayHelper::getColumn(Category::find()->where(['in', 'id', \common\models\RelationCategory::find()->
                                    select('category_id')->
                                    where(['rest_org_id' => $currentUser->organization_id,
                                        'supp_org_id' => $supplier_org_id])])->all(), 'id');
        if (Yii::$app->request->isAjax) {
            if (!empty($user)) {
                $post = Yii::$app->request->post();
                if ($user->status == 0 && $post) {
                    $organization->load($post);
                    if ($organization->validate()) {
                        $organization->save();
                        if ($user->email != $organization->email) {
                            $user->email = $organization->email;
                            $user->save();
                            $currentUser->sendInviteToVendor($user);
                        }/* else {
                            if (Yii::$app->request->post('resend_email') == 1) {
                                $currentUser->sendInviteToVendor($user);
                            }
                        }*/
                    } else {
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
            } else {
                $post = Yii::$app->request->post();
                if ($post) {
                    $sql = "DELETE FROM relation_category WHERE rest_org_id=$currentUser->organization_id AND supp_org_id=$supplier_org_id";
                    \Yii::$app->db->createCommand($sql)->execute();
                    $message = 'Сохранено';
                    return $this->renderAjax('suppliers/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('suppliers/_viewSupplier', compact('organization', 'supplier_org_id', 'currentUser', 'load_data', 'user', 'userStatus'));
    }

    public function actionReSendEmailInvite($id) {
        if (Yii::$app->request->isAjax) {
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $organization = Organization::find()->where(['id' => $id])->one();
            $user = User::find()->where(['email' => $organization->email])->one();
            $currentUser->sendInviteToVendor($user);
        }
    }

    public function actionViewCatalog($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::BASE_CATALOG) {
            $query = Yii::$app->db->createCommand("SELECT catalog.id as id,article,catalog_base_goods.product as product,units,ed,catalog_base_goods.price,catalog_base_goods.status "
                    . " FROM `catalog` "
                    . " JOIN catalog_base_goods on catalog.id = catalog_base_goods.cat_id"
                    . " WHERE "
                    . " catalog_base_goods.cat_id = $id and deleted != 1");
            $totalCount = Yii::$app->db->createCommand(" SELECT COUNT(*) "
                            . " FROM `catalog` "
                            . " JOIN catalog_base_goods on catalog.id = catalog_base_goods.cat_id"
                            . " WHERE "
                            . " catalog_base_goods.cat_id = $id and deleted != 1")->queryScalar();
        }
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::CATALOG) {
            $query = Yii::$app->db->createCommand("SELECT catalog.id as id,article,catalog_base_goods.product as product,units,ed,catalog_goods.price as price, catalog_base_goods.status "
                    . " FROM `catalog` "
                    . " JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                    . " JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id"
                    . " WHERE "
                    . " catalog_goods.cat_id = $id and deleted != 1");
            $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) "
                            . " FROM `catalog` "
                            . " JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                            . " JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id"
                            . " WHERE "
                            . " catalog_goods.cat_id = $id and deleted != 1")->queryScalar();
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 7,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'article',
                    'product',
                    'units',
                    'price',
                    'status',
                    'ed'
                ],
                'defaultOrder' => [
                    'product' => SORT_DESC
                ]
            ],
        ]);
        return $this->renderAjax('suppliers/_viewCatalog', compact('searchModel', 'dataProvider', 'cat_id'));
    }

    public function actionEditCatalog($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $supp_org_id = Catalog::find()->where(['id' => $id])->one()->supp_org_id;
        $catalog_id = $id;
        $base_catalog_id = Catalog::find()->where(['supp_org_id' => $supp_org_id, 'type' => 1])->one()->id;
        if (Yii::$app->request->isAjax && Yii::$app->request->post('catalog')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            if ($arrCatalog === Array()) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Каталог пустой']];
                    return $result;
                    exit;
                }
                if(count($arrCatalog)>1000){
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'Уведомление',
                            'body' => 'Чтобы добавить больше <strong>1000</strong> позиций, пожалуйста свяжитесь с нами '
                   . '<a href="mailto://info@f-keeper.ru" target="_blank" class="text-success">info@f-keeper.ru</a>']];
                    return $result;
                    exit;     
                }
            foreach ($arrCatalog as $arrCatalogs) {
                $product = trim($arrCatalogs['dataItem']['product']);
                $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                $units = trim($arrCatalogs['dataItem']['units']);
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                $ed = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
                $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                if (empty($article)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => '<strong>[Артикул]</strong> не указан']];
                    return $result;
                    exit;
                }
                if (empty($product)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Пустое поле <strong>[Наименование]</strong>!']];
                    return $result;
                    exit;
                }
                if (empty($price)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Пустое поле <strong>[Цена]</strong>!']];
                    return $result;
                    exit;
                }
                $price = str_replace(',', '.', $price);
                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => '<strong>[Цена]</strong> в неверном формате!']];
                    return $result;
                    exit;
                }
                if (empty($units) || $units < 0) {
                    $units = 0;
                }
                $units = str_replace(',', '.', $units);
                if (!empty($units) && !preg_match($numberPattern, $units)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => '<strong>[Кратность]</strong> товара в неверном формате']];
                    return $result;
                    exit;
                }
                if (empty($ed)) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Пустое поле <strong>[Единица измерения]</strong>!']];
                    return $result;
                    exit;
                }
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $sql = "delete gn " .
                        "from goods_notes gn " .
                        "inner join catalog_goods cg " .
                        "on gn.catalog_base_goods_id = cg.base_goods_id " .
                        "where cg.cat_id=$catalog_id";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "delete cb " .
                        "from catalog_base_goods cb " .
                        "inner join catalog_goods c " .
                        "on cb.id=c.base_goods_id  " .
                        "where cb.supp_org_id=$supp_org_id and c.cat_id=$catalog_id";
                \Yii::$app->db->createCommand($sql)->execute();
                $sql = "delete from catalog_goods where cat_id=$catalog_id";
                \Yii::$app->db->createCommand($sql)->execute();
                foreach ($arrCatalog as $arrCatalogs) {
                    $product = trim($arrCatalogs['dataItem']['product']);
                    $article = trim($arrCatalogs['dataItem']['article']);
                    $units = trim($arrCatalogs['dataItem']['units']);
                    $price = trim($arrCatalogs['dataItem']['price']);
                    $ed = trim($arrCatalogs['dataItem']['ed']);
                    $note = trim($arrCatalogs['dataItem']['note']);

                    $sql = "insert into {{%catalog_base_goods}}" .
                            "(`cat_id`,`supp_org_id`,`article`,`product`,"
                            . "`units`,`price`,`ed`,`status`,`created_at`) VALUES ("
                            . ":cat_id,"
                            . $supp_org_id . ","
                            . ":article,"
                            . ":product,"
                            . ":units,"
                            . ":price,"
                            . ":ed,"
                            . CatalogBaseGoods::STATUS_ON . ","
                            . "NOW())";
                    $command = \Yii::$app->db->createCommand($sql);
                    $command->bindParam(":cat_id", $base_catalog_id, \PDO::PARAM_INT);
                    $command->bindParam(":article", $article, \PDO::PARAM_STR);
                    $command->bindParam(":product", $product, \PDO::PARAM_STR);
                    $command->bindParam(":units", $units);
                    $command->bindParam(":price", $price);
                    $command->bindParam(":ed", $ed, \PDO::PARAM_STR);
                    $command->execute();
                    $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();

                    $sql = "insert into {{%catalog_goods}}" .
                            "(`cat_id`,`base_goods_id`,`price`,`created_at`) VALUES ("
                            . ":cat_id,"
                            . $lastInsert_base_goods_id . ","
                            . ":price,"
                            . "NOW())";
                    $command = \Yii::$app->db->createCommand($sql);
                    $command->bindParam(":cat_id", $catalog_id, \PDO::PARAM_INT);
                    $command->bindParam(":price", $price);
                    $command->execute();

                    if (!empty($note)) {
                        $sql = "insert into " . GoodsNotes::tableName() .
                                " (`rest_org_id`,`catalog_base_goods_id`,`note`,`created_at`) VALUES ("
                                . $currentUser->organization_id . ","
                                . $lastInsert_base_goods_id . ","
                                . ":note,"
                                . "NOW())";
                        $command = \Yii::$app->db->createCommand($sql);
                        $command->bindParam(":note", $note, \PDO::PARAM_STR);
                        $command->execute();
                    }
                }
                $transaction->commit();
                $result = ['success' => false, 'alert' => [
                        'class' => 'success-fk',
                        'title' => 'Сохранено',
                        'body' => 'Каталог был успешно обновлен']];
                return $result;
                exit;
            } catch (Exception $e) {
                $transaction->rollback();
                $result = ['success' => false, 'alert' => [
                        'class' => 'danger-fk',
                        'title' => 'Ошибка сохранения',
                        'body' => 'Пожалуйста, повторите попытку сохранения']];
                return $result;
                exit;
            }
            //$message =  'Успех';   
            //return $this->renderAjax('suppliers/_success', ['message' => $message]);
        }
        $sql = "SELECT "
                . "catalog.id as catalog_id,"
                . "catalog_base_goods.id as goods_base_id,"
                . "article,"
                . "product,"
                . "units,"
                . "ed,"
                . "catalog_base_goods.price,"
                . "goods_notes.note"
                . " FROM `catalog` "
                . "LEFT JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                . "LEFT JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id "
                . "LEFT JOIN goods_notes on catalog_base_goods.id = goods_notes.catalog_base_goods_id "
                . "WHERE catalog.id = $id";
        $arr = \Yii::$app->db->createCommand($sql)->queryAll();
        $array = [];
        foreach ($arr as $arrs) {
            $c_catalog_id = $arrs['catalog_id'];
            $c_goods_base_id = $arrs['goods_base_id'];
            $c_article = $arrs['article'];
            $c_product = $arrs['product'];
            $c_units = $arrs['units'];
            $c_ed = $arrs['ed'];
            $c_price = $arrs['price'];
            $c_note = $arrs['note'];
            array_push($array, [
                'catalog_id' => $c_catalog_id,
                'goods_base_id' => $c_goods_base_id,
                'article' => $c_article,
                'product' => $c_product,
                'units' => $c_units,
                'ed' => $c_ed,
                'price' => $c_price,
                'note' => $c_note]);
        }

        return $this->renderAjax('suppliers/_editCatalog', compact('id', 'array'));
    }

    public function actionRemoveSupplier($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $sql = "delete from relation_supp_rest where rest_org_id =$currentUser->organization_id and supp_org_id = $id";
        \Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from relation_category where rest_org_id =$currentUser->organization_id and supp_org_id = $id";
        \Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionMessages() {
        return $this->render('/site/underConstruction');
    }

    public function actionAnalytics() {
        $currentUser = User::findIdentity(Yii::$app->user->id);

        $header_info_zakaz = \common\models\Order::find()->
                        where(['client_id' => $currentUser->organization_id])->count();
        empty($header_info_zakaz) ? $header_info_zakaz = 0 : $header_info_zakaz = (int)$header_info_zakaz;
        $header_info_suppliers = \common\models\RelationSuppRest::find()->
                        where(['rest_org_id' => $currentUser->organization_id, 'invite' => RelationSuppRest::INVITE_ON])->count();
        empty($header_info_suppliers) ? $header_info_suppliers = 0 : $header_info_suppliers = (int)$header_info_suppliers;
        $header_info_purchases = \common\models\Order::find()->
                        where(['client_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])->count();
        empty($header_info_purchases) ? $header_info_purchases = 0 : $header_info_purchases = (int)$header_info_purchases;
        $header_info_items = \common\models\OrderContent::find()->select('sum(quantity) as quantity')->
                        where(['in', 'order_id', \common\models\Order::find()->select('id')->where(['client_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])])->one()->quantity;
        empty($header_info_items) ? $header_info_items = 0 : $header_info_items = (int)$header_info_items;
        $filter_get_supplier = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                where(['in', 'id', \common\models\RelationSuppRest::find()->
                                    select('supp_org_id')->
                                    where(['rest_org_id' => $currentUser->organization_id, 'invite' => '1'])])->all(), 'id', 'name');

        $filter_get_employee = yii\helpers\ArrayHelper::map(\common\models\Profile::find()->
                                where(['in', 'user_id', \common\models\User::find()->
                                    select('id')->
                                    where(['organization_id' => $currentUser->organization_id])])->all(), 'user_id', 'full_name');
        $filter_status = "";
        $filter_from_date = date("d-m-Y", strtotime(" -2 months"));
        $filter_to_date = date("d-m-Y");
        $where = "";

        //pieChart
        function hex() {
            $hex = '#';
            foreach (array('r', 'g', 'b') as $color) {
                //случайное число в диапазоне 0 и 255.
                $val = mt_rand(0, 255);
                //преобразуем число в Hex значение.
                $dechex = dechex($val);
                //с 0, если длина меньше 2
                if (strlen($dechex) < 2) {
                    $dechex = "0" . $dechex;
                }
                //объединяем
                $hex .= $dechex;
            }
            return $hex;
        }

        if (Yii::$app->request->isAjax) {
            $filter_status = trim(\Yii::$app->request->get('filter_status'));
            $filter_supplier = trim(\Yii::$app->request->get('filter_supplier'));
            $filter_employee = trim(\Yii::$app->request->get('filter_employee'));
            $filter_from_date = trim(\Yii::$app->request->get('filter_from_date'));
            $filter_to_date = trim(\Yii::$app->request->get('filter_to_date'));

            empty($filter_status) ? "" : $where .= " and status='" . $filter_status . "'";
            empty($filter_supplier) ? "" : $where .= " and vendor_id='" . $filter_supplier . "'";
            empty($filter_employee) ? "" : $where .= " and created_by_id='" . $filter_employee . "'";
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
        $arr_create_at = [];
        $arr_price = [];
        if (count($area_chart) == 1) {
            array_push($arr_create_at, 0);
            array_push($arr_price, 0);
        }

        foreach ($area_chart as $area_charts) {
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
        $vendors_total_price = [];
        foreach ($vendors_total_price_sql as $vendors_total_price_sql_arr) {
            $arr = array(
                'value' => $vendors_total_price_sql_arr['total_price'],
                'label' => \common\models\Organization::find()->where(['id' => $vendors_total_price_sql_arr['vendor_id']])->one()->name,
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
        $chart_bar_value = [];
        $chart_bar_label = [];
        foreach ($vendors_total_price_sql as $vendors_bar_total_price_sql_arr) {
            $arr = array($vendors_bar_total_price_sql_arr['total_price']);
            array_push($chart_bar_value, $arr);
            $arr = array(\common\models\Organization::find()->where(['id' => $vendors_bar_total_price_sql_arr['vendor_id']])->one()->name);
            array_push($chart_bar_label, $arr);
        }
        $chart_bar_value = json_encode($chart_bar_value);
        $chart_bar_label = json_encode($chart_bar_label);
        /*
         * 
         * BarChart заказы по поставщикам END
         * 
         */
        return $this->render('analytics/index', compact(
                                'header_info_zakaz', 'header_info_suppliers', 'header_info_purchases', 'header_info_items', 'filter_get_supplier', 'filter_get_employee', 'filter_supplier', 'filter_employee', 'filter_status', 'filter_from_date', 'filter_to_date', 'arr_create_at', 'arr_price', 'vendors_total_price', 'dataProvider', 'chart_bar_value', 'chart_bar_label'
        ));
    }

    public function actionTutorial() {
        return $this->render('tutorial');
    }
    
    public function actionTest() {
        return $this->render('tutorial');
    }

    public function actionSupport() {
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
        $suppliers_where = "";
        /*
         * 
         * Поставщики
         * 
         */
        $searchString = "";
        $where = "";
        if (Yii::$app->request->isAjax) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";

            empty($searchString) ? "" : $where .= " and name LIKE :name";
        }
        $sql = "SELECT supp_org_id, name FROM `relation_supp_rest` join `organization`
on `relation_supp_rest`.`supp_org_id` = `organization`.`id` WHERE "
                . "rest_org_id = $currentUser->organization_id and invite = " . RelationSuppRest::INVITE_ON . "$where";
        $query = \Yii::$app->db->createCommand($sql);
        $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM `relation_supp_rest` join `organization`
on `relation_supp_rest`.`supp_org_id` = `organization`.`id` WHERE "
                        . "rest_org_id = $currentUser->organization_id and invite = " . RelationSuppRest::INVITE_ON . "$where", [':name' => $searchString])->queryScalar();

        $suppliers_dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'params' => [':name' => $searchString],
            'pagination' => [
                'pageSize' => 4,
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
        // chart АНАЛИТИКА по неделям прошедшим
        $curent_monday = date('Y-m-d', strtotime(date('Y') . 'W' . date('W') . '1')); // текущая неделя - понедельник
        $curent_sunday = date('Y-m-d', strtotime(date('Y') . 'W' . date('W') . '7')); // текущая неделя - воскресение
        $i = 0;
        $max_i = 5; //Сколько недель показывать от текущей
        $mon = 0;
        $sun = 6;
        $query = "";
        while ($i < $max_i + 1) {
            $i++;
            $while_monday = date('Y-m-d', strtotime("$curent_monday $mon day"));
            $while_sunday = date('Y-m-d', strtotime("$curent_monday $sun day"));
            $dates = date('m/d', strtotime("$curent_monday $sun day"));
            ;
            $query .="SELECT sum(total_price) as price,'$dates' as dates from `order` where "
                    . "client_id = $currentUser->organization_id and ("
                    . "DATE(created_at) between '" .
                    date('Y-m-d', strtotime($while_monday)) . "' and '" .
                    date('Y-m-d', strtotime($while_sunday)) . "') ";
            $i > $max_i ? "" : $query .=" UNION ALL \n";
            $mon = $mon - 7;
            $sun = $sun - 7;
        }
        $query = Yii::$app->db->createCommand($query)->queryAll();
        $chart_dates = [];
        $chart_price = [];
        foreach ($query as $querys) {
            if (empty($querys['price'])) {
                array_push($chart_price, 0);
            } else {
                array_push($chart_price, $querys['price']);
            }
            array_push($chart_dates, $querys['dates']);
        }
        // var_dump($chart_price);
        return $this->render('dashboard/index', compact(
                                'dataProvider', 'suppliers_dataProvider', 'chart_dates', 'chart_price'
        ));
    }

    public function actionSuppliers() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $step = $currentUser->organization->step;
        $user = new User;
        $profile = new Profile;
        $relationCategory = new RelationCategory;
        $organization = new Organization;
        $searchString = "";
        $where = "";
        if (Yii::$app->request->isAjax) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
            empty(trim(\Yii::$app->request->get('searchString'))) ? "" : $where .= " and organization.name LIKE :name";
        }
        $query = Yii::$app->db->createCommand("SELECT 
            relation_supp_rest.id,
            organization.name as 'organization_name',
            relation_supp_rest.cat_id,
            catalog.name as 'catalog_name',
            relation_supp_rest.created_at,
            relation_supp_rest.supp_org_id,
            invite,
            case 
                when invite=0 then 1 else
                    case when (select count(*) from user where email=`organization`.`email` and status =0) = 1 then 2 else 3 end
                    end as status_invite,
            `relation_supp_rest`.`status` 
            FROM {{%relation_supp_rest}}"
                . "JOIN `organization` on `relation_supp_rest`.`supp_org_id` = `organization`.`id` "
                . "LEFT OUTER JOIN `catalog` on `relation_supp_rest`.`cat_id` = `catalog`.`id` "
                . "WHERE rest_org_id = " . $currentUser->organization_id . " $where");
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM "
                        . "(SELECT `relation_supp_rest`.id FROM {{%relation_supp_rest}} "
                        . "JOIN `organization` on `relation_supp_rest`.`supp_org_id` = `organization`.`id` "
                        . "LEFT OUTER JOIN `catalog` on `relation_supp_rest`.`cat_id` = `catalog`.`id` "
                        . "WHERE rest_org_id = " . $currentUser->organization_id . " $where)`tb`", [':name' => $searchString])->queryScalar();
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'params' => [':name' => $searchString],
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'supp_org_id',
                    'cat_id',
                    'invite',
                    'status',
                    'created_at',
                    'organization_name',
                    'catalog_name',
                    'status_invite'
                ],
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ],
        ]);
        return $this->render("suppliers", compact("user", "organization", "relationCategory", "profile", "searchModel", "searchString", "dataProvider", "step"));
    }

    public function actionSidebar() {
        Yii::$app->session->get('sidebar-collapse') ?
                        Yii::$app->session->set('sidebar-collapse', false) :
                        Yii::$app->session->set('sidebar-collapse', true);
    }

}
