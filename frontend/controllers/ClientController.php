<?php

namespace frontend\controllers;

use Yii;
use yii\web\UploadedFile;
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
//                'only' => ['index', 'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'suppliers', 'tutorial', 'employees'],
                'rules' => [
                    [
                        'actions' => [
                            'settings',
                            'ajax-create-user',
                            'ajax-delete-user',
                            'ajax-update-user',
                            'ajax-validate-user',
                            'ajax-validate-vendor',
                            'employees',
                            'remove-supplier',
                            'add-first-vendor',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => [
                            'index',
                            'suppliers',
                            'tutorial',
                            'analytics',
                            'chkmail',
                            'create',
                            'edit-catalog',
                            'events',
                            'invite',
                            'messages',
                            're-send-email-invite',
                            'sidebar',
                            'support',
                            'view-catalog',
                            'view-supplier',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
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
        $organization->scenario = "settings";
        if ($organization->load(Yii::$app->request->post())) {
            if ($organization->validate()) {
                $organization->address = $organization->formatted_address;
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

                    if (!in_array($user->role_id, User::getAllowedRoles($this->currentUser->role_id))) {
                        $user->role_id = $this->currentUser->role_id;
                    }

                    $user->setRegisterAttributes($user->role_id)->save();
                    $profile->setUser($user->id)->save();
                    $user->setOrganization($this->currentUser->organization)->save();
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
        $oldRole = $user->role_id;
        $profile = $user->profile;
        $organizationType = $user->organization->type_id;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    if (!in_array($user->role_id, User::getAllowedRoles($oldRole))) {
                        $user->role_id = $oldRole;
                    } elseif ($user->role_id == Role::ROLE_RESTAURANT_EMPLOYEE && $oldRole == Role::ROLE_RESTAURANT_MANAGER && $user->organization->managersCount == 1) {
                        $user->role_id = $oldRole;
                    }
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
                $user = User::findOne(['id' => $post['id']]);
                $usersCount = count($user->organization->users);
                if ($user->id == $this->currentUser->id) {
                    $message = 'Может воздержимся от удаления себя?';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1)) {
//                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save()) {
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
            $relationSuppRest = new RelationSuppRest();
            $relationCategory = new RelationCategory();
            $organization = new Organization();
            $profile = new Profile();

            $post = Yii::$app->request->post();
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post); //name

            $organization->type_id = Organization::TYPE_SUPPLIER; //org type_id
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
                if (count($arrCatalog) > 300) {
                    $result = ['success' => false, 'message' => 'Чтобы добавить больше <strong>300</strong> позиций, пожалуйста свяжитесь с нами '
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="text-success">info@f-keeper.ru</a>'];
                    return $result;
                    exit;
                }
                foreach ($arrCatalog as $arrCatalogs) {
                    $product = trim($arrCatalogs['dataItem']['product']);
                    //$article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                    //$units = trim($arrCatalogs['dataItem']['units']);
                    $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                    $ed = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
//                    if (empty($article)) {
//                        $result = ['success' => false, 'message' => 'Ошибка: <strong>[Артикул]</strong> не указан'];
//                        return $result;
//                        exit;
//                    }
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
                        $user->setRegisterAttributes(Role::getManagerRole($organization->type_id));
                        $user->status = User::STATUS_UNCONFIRMED_EMAIL;
                        $user->save();
                        $profile->setUser($user->id);
                        $profile->sms_allow = Profile::SMS_ALLOW;
                        $profile->save();
                        $organization->save();
                        $user->setOrganization($organization)->save();
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
                        $newBaseCatalog = new Catalog();
                        $newBaseCatalog->supp_org_id = $get_supp_org_id;
                        $newBaseCatalog->name = 'Главный каталог';
                        $newBaseCatalog->type = Catalog::BASE_CATALOG;
                        $newBaseCatalog->status = Catalog::STATUS_ON;
                        $newBaseCatalog->save();
                        $newBaseCatalog->refresh();
                        $lastInsert_base_cat_id = $newBaseCatalog->id;
                    } else {
                        //Поставщик зарегистрирован, но не авторизован
                        //проверяем, есть ли у поставщика Главный каталог и если нету, тогда создаем ему каталог
                        if (Catalog::find()->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
                            $lastInsert_base_cat_id = Catalog::find()->select('id')->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->one();
                            $lastInsert_base_cat_id = $lastInsert_base_cat_id['id'];
                        } else {
                            $newBaseCatalog = new Catalog();
                            $newBaseCatalog->supp_org_id = $get_supp_org_id;
                            $newBaseCatalog->name = 'Главный каталог';
                            $newBaseCatalog->type = Catalog::BASE_CATALOG;
                            $newBaseCatalog->status = Catalog::STATUS_ON;
                            $newBaseCatalog->save();
                            $newBaseCatalog->refresh();
                            $lastInsert_base_cat_id = $newBaseCatalog->id;
                        }
                    }

                    $newCatalog = new Catalog();
                    $newCatalog->supp_org_id = $get_supp_org_id;
                    $newCatalog->name = $currentUser->organization->name;
                    $newCatalog->type = Catalog::CATALOG;
                    $newCatalog->status = Catalog::STATUS_ON;
                    $newCatalog->save();
                    $newCatalog->refresh();

                    $lastInsert_cat_id = $newCatalog->id;
                    /**
                     *
                     * 3 и 4) Создаем каталог базовый и его продукты, создаем новый каталог для ресторана и забиваем продукты на основе базового каталога
                     *    
                     * */
                    foreach ($arrCatalog as $arrCatalogs) {
                        $article = "".rand(10000, 99999);//trim($arrCatalogs['dataItem']['article']);
                        $product = trim($arrCatalogs['dataItem']['product']);
                        $units = null;//htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                        $units = str_replace(',', '.', $units);
//                        if (substr($units, -3, 1) == '.') {
//                            $units = explode('.', $units);
//                            $last = array_pop($units);
//                            $units = join($units, '') . '.' . $last;
//                        } else {
//                            $units = str_replace('.', '', $units);
//                        }
                        if (empty($units) || $units < 0) {
                            $units = null;
                        }
                        $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                        //$note = trim($arrCatalogs['dataItem']['note']);
                        $ed = trim($arrCatalogs['dataItem']['ed']);
                        $price = str_replace(',', '.', $price);
                        if (substr($price, -3, 1) == '.') {
                            $price = explode('.', $price);
                            $last = array_pop($price);
                            $price = join($price, '') . '.' . $last;
                        } else {
                            $price = str_replace('.', '', $price);
                        }
                        $newProduct = new CatalogBaseGoods();
                        $newProduct->cat_id = $lastInsert_base_cat_id;
                        $newProduct->supp_org_id = $get_supp_org_id;
                        $newProduct->article = $article;
                        $newProduct->product = $product;
                        $newProduct->units = $units;
                        $newProduct->price = $price;
                        $newProduct->ed = $ed;
                        $newProduct->status = CatalogBaseGoods::STATUS_ON;
                        $newProduct->market_place = CatalogBaseGoods::MARKETPLACE_OFF;
                        $newProduct->deleted = CatalogBaseGoods::DELETED_OFF;
                        $newProduct->save();
                        $newProduct->refresh();
                        
                        $lastInsert_base_goods_id = $newProduct->id;

                        $sql = "insert into " . CatalogGoods::tableName() . "(
				      `cat_id`,`base_goods_id`,`price`,`discount`,`created_at`) VALUES (
				      $lastInsert_cat_id, $lastInsert_base_goods_id, '$price', 0,NOW())";
                        $lastInsert_goods_id = Yii::$app->db->getLastInsertID();
                        \Yii::$app->db->createCommand($sql)->execute();

                    }

                    /**
                     *  
                     * 5) Связь ресторана и поставщика
                     *     
                     * */
                    $relationSuppRest->rest_org_id = $currentUser->organization_id;
                    $relationSuppRest->supp_org_id = $get_supp_org_id;
                    $relationSuppRest->cat_id = $lastInsert_cat_id;
                    $relationSuppRest->status = 1;
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    if (isset($relationSuppRest->uploaded_catalog)) {
                        $relationSuppRest->uploaded_processed = 0;
                    }
                    $relationSuppRest->save();
                    /**
                     *
                     * Отправка почты
                     * 
                     * */
                    $currentUser->sendInviteToVendor($user);
                    $currentOrganization = $currentUser->organization;
                    $currentOrganization->step = Organization::STEP_OK;
                    $currentOrganization->save();

                    if (!empty($profile->phone)) {
                        $text = 'Ресторан ' . $currentUser->organization->name . ' приглашает Вас в систему f-keeper.ru';
                        $target = $profile->phone;
                        $sms = new \common\components\QTSMS();
                        $sms->post_message($text, $target);
                    }
                    if ($check['eventType'] == 5) {
                        $result = ['success' => true, 'message' => 'Поставщик ' . $organization->name . ' и каталог добавлен! Инструкция по авторизации была отправлена на почту ' . $email . ''];
                        return $result;
                    } else {
                        $result = ['success' => true, 'message' => 'Каталог добавлен! приглашение было отправлено на почту  ' . $email . ''];
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

            $post = Yii::$app->request->post();
            $user->load($post); //user-email
            $profile->load($post); //profile-full_name
            $organization->load($post); //name
            $organization->type_id = Organization::TYPE_SUPPLIER; //org type_id
//            $relationCategory->load($post); //array category
            $currentUser = User::findIdentity(Yii::$app->user->id);

            if ($user->validate() && $profile->validate() && $organization->validate()) {
                if ($check['eventType'] == 6) {
                    $email = $user->email;
                    $fio = $profile->full_name;
                    $org = $organization->name;
//                    $categorys = $relationCategory['category_id'];
                    $get_supp_org_id = $check['org_id'];

                    //check deleted relation
                    $relationSuppRest = RelationSuppRest::findOne([
                                'rest_org_id' => $currentUser->organization_id,
                                'supp_org_id' => $get_supp_org_id,
                                'deleted' => true
                    ]);

                    if (empty($relationSuppRest)) {
                        $relationSuppRest = new RelationSuppRest;
                    } else {
                        $relationSuppRest->deleted = false;
                    }

                    if (Catalog::find()->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
                        $supp_base_cat_id = Catalog::find()->where(['supp_org_id' => $get_supp_org_id, 'type' => 1])->one()->id;
                        $relationSuppRest->cat_id = $supp_base_cat_id;
                    }
                    $relationSuppRest->rest_org_id = $currentUser->organization_id;
                    $relationSuppRest->supp_org_id = $get_supp_org_id;
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    $relationSuppRest->save();
//                    if (!empty($categorys)) {
//                        foreach ($categorys as $arrCategorys) {
//                            $sql = "insert into " . RelationCategory::tableName() . "(`category_id`,`rest_org_id`,`supp_org_id`,`created_at`) VALUES ('$arrCategorys',$currentUser->organization_id,$get_supp_org_id,NOW())";
//                            \Yii::$app->db->createCommand($sql)->execute();
//                        }
//                    }
                    $result = ['success' => true, 'message' => 'Приглашение отправлено!'];
                    $currentOrganization = $currentUser->organization;

                    $rows = User::find()->where(['organization_id' => $get_supp_org_id])->all();
                    
                    $mailer = Yii::$app->mailer; 
                    $email = $row->email;
                    $subject = "Ресторан " . $currentOrganization->organization->name . " приглашает вас в систему f-keeper.ru";
                    $mailer->htmlLayout = 'layouts/html';
                    $result = $mailer->compose('ClientInviteSupplier', compact("currentOrganization"))
                            ->setTo($email)->setSubject($subject)->send();
                    
                    foreach ($rows as $row) {
                        if ($row->profile->phone && $row->profile->sms_allow) {
                            $text = 'Ресторан ' . $currentUser->organization->name . ' хочет работать с Вами в системе f-keeper.ru';
                            $target = $row->profile->phone;
                            $sms = new \common\components\QTSMS();
                            $sms->post_message($text, $target);
                        }
                    }
                    

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
        $user = User::find()->where(['organization_id' => $organization->id])->one();
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
                          } */
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
            foreach ($organization->users as $recipient) {
                $currentUser->sendInviteToVendor($recipient);
                if ($recipient->profile->phone && $recipient->profile->sms_allow) {
                    $text = "Повторное приглашение в систему F-keeper от " . $currentUser->organization->name;
                    $target = $recipient->profile->phone;
                    $sms = new \common\components\QTSMS();
                    $sms->post_message($text, $target);
                }
            }
        }
    }

    public function actionViewCatalog($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);

        if (Catalog::find()->where(['id' => $cat_id, 'status' => 1])->one()->type == Catalog::BASE_CATALOG) {
            $query = Yii::$app->db->createCommand("SELECT catalog.id as id,article,catalog_base_goods.product as product,units,ed,catalog_base_goods.price,catalog_base_goods.status "
                    . " FROM `catalog` "
                    . " JOIN catalog_base_goods on catalog.id = catalog_base_goods.cat_id"
                    . " WHERE "
                    . " catalog_base_goods.cat_id = $id and deleted = " . CatalogBaseGoods::DELETED_OFF);
            $totalCount = Yii::$app->db->createCommand(" SELECT COUNT(*) "
                            . " FROM `catalog` "
                            . " JOIN catalog_base_goods on catalog.id = catalog_base_goods.cat_id"
                            . " WHERE "
                            . " catalog_base_goods.cat_id = $id and deleted = " . CatalogBaseGoods::DELETED_OFF)->queryScalar();
        }
        if (Catalog::find()->where(['id' => $cat_id, 'status' => 1])->one()->type == Catalog::CATALOG) {
            $query = Yii::$app->db->createCommand("SELECT catalog.id as id,article,catalog_base_goods.product as product,units,ed,catalog_goods.price as price, catalog_base_goods.status "
                    . " FROM `catalog` "
                    . " JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                    . " JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id"
                    . " WHERE "
                    . " catalog_goods.cat_id = $id and deleted = " . CatalogBaseGoods::DELETED_OFF);
            $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) "
                            . " FROM `catalog` "
                            . " JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                            . " JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id"
                            . " WHERE "
                            . " catalog_goods.cat_id = $id and deleted = " . CatalogBaseGoods::DELETED_OFF)->queryScalar();
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
        $catalog_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $supp_org_id = Catalog::find()->where(['id' => $catalog_id])->one()->supp_org_id;
        $supplier = Organization::find()->where(['id' => $supp_org_id])->one();

        $catalog = CatalogGoods::find()->where(['cat_id' => $catalog_id])->all();
        $array_base_goods_id = ArrayHelper::getColumn($catalog, 'base_goods_id');
        $array_goods_id = ArrayHelper::getColumn($catalog, 'id');

        $base_catalog = Catalog::find()->where(['supp_org_id' => $supplier->id, 'type' => Catalog::BASE_CATALOG])->one();
        $arr_check_double_article = [];
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
            if (count($arrCatalog) > 5000) {
                $result = ['success' => false, 'alert' => [
                        'class' => 'danger-fk',
                        'title' => 'Уведомление',
                        'body' => 'Чтобы добавить/обновить более <strong>5000</strong> позиций, пожалуйста свяжитесь с нами '
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="text-success">info@f-keeper.ru</a>']];
                return $result;
                exit;
            }
            foreach ($arrCatalog as $arrCatalogs) {
                $base_goods_id = trim($arrCatalogs['dataItem']['base_goods_id']);
                $goods_id = trim($arrCatalogs['dataItem']['goods_id']);
                $product = trim($arrCatalogs['dataItem']['product']);
                $article = trim($arrCatalogs['dataItem']['article']);
                $units = trim($arrCatalogs['dataItem']['units']);
                $price = trim($arrCatalogs['dataItem']['price']);
                $ed = trim($arrCatalogs['dataItem']['ed']);
                $note = trim($arrCatalogs['dataItem']['note']);
                if (
                        !empty($base_goods_id) &&
                        !ArrayHelper::isIn($base_goods_id, $array_base_goods_id)
                ) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Ай ай ай как не хорошо!']];
                    return $result;
                    exit;
                }
                if (
                        !empty($goods_id) &&
                        !ArrayHelper::isIn($goods_id, $array_goods_id)
                ) {
                    $result = ['success' => false, 'alert' => [
                            'class' => 'danger-fk',
                            'title' => 'УПС! Ошибка',
                            'body' => 'Ай ай ай как не хорошо!']];
                    return $result;
                    exit;
                }
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
                array_push($arr_check_double_article, $arrCatalogs['dataItem']['article']);
            }
            if (array_diff(array_count_values($arr_check_double_article), array('1'))) {
                $result = ['success' => false, 'alert' => [
                        'class' => 'danger-fk',
                        'title' => 'УПС! Ошибка',
                        'body' => 'Артикул товара должен быть уникальным!']];
                return $result;
                exit;
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $array_ids = [];
                foreach ($arrCatalog as $arrCatalogs) {
                    $base_goods_id = trim($arrCatalogs['dataItem']['base_goods_id']);
                    $goods_id = trim($arrCatalogs['dataItem']['goods_id']);
                    $product = trim($arrCatalogs['dataItem']['product']);
                    $article = trim($arrCatalogs['dataItem']['article']);
                    $units = trim($arrCatalogs['dataItem']['units']);
                    $price = trim($arrCatalogs['dataItem']['price']);
                    $ed = trim($arrCatalogs['dataItem']['ed']);
                    $note = trim($arrCatalogs['dataItem']['note']);
                    //сравниваем массивы каталога и пришедший массив
                    //Если пришедший ID п есть в массиве каталога 
                    if (!ArrayHelper::isIn($goods_id, $array_goods_id)) {

                        $CatalogBaseGoods = new CatalogBaseGoods();
                        $CatalogBaseGoods->cat_id = $base_catalog->id;
                        $CatalogBaseGoods->supp_org_id = $supp_org_id;
                        $CatalogBaseGoods->article = $article;
                        $CatalogBaseGoods->status = CatalogBaseGoods::STATUS_ON;
                        $CatalogBaseGoods->product = $product;
                        $CatalogBaseGoods->units = $units;
                        $CatalogBaseGoods->price = $price;
                        $CatalogBaseGoods->ed = $ed;
                        $CatalogBaseGoods->save();

                        $CatalogGoods = new CatalogGoods();
                        $CatalogGoods->cat_id = $catalog_id;
                        $CatalogGoods->base_goods_id = $CatalogBaseGoods->id;
                        $CatalogGoods->price = $price;
                        $CatalogGoods->save();

                        if (!empty($note)) {
                            $GoodsNotes = new GoodsNotes();
                            $GoodsNotes->rest_org_id = $currentUser->organization_id;
                            $GoodsNotes->catalog_base_goods_id = $CatalogBaseGoods->id;
                            $GoodsNotes->note = $note;
                            $GoodsNotes->save();
                        }
                    } else {
                        $CatalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $base_goods_id])->one();
                        $CatalogBaseGoods->article = $article;
                        $CatalogBaseGoods->status = CatalogBaseGoods::STATUS_ON;
                        $CatalogBaseGoods->product = $product;
                        $CatalogBaseGoods->units = $units;
                        $CatalogBaseGoods->price = $price;
                        $CatalogBaseGoods->ed = $ed;
                        $CatalogBaseGoods->save();

                        $CatalogGoods = CatalogGoods::find()->where(['id' => $goods_id])->one();
                        $CatalogGoods->price = $price;
                        $CatalogGoods->save();
                        if (!empty($note)) {
                            if ($GoodsNotes = GoodsNotes::find()->where([
                                        'rest_org_id' => $currentUser->organization_id,
                                        'catalog_base_goods_id' => $CatalogBaseGoods->id
                                    ])->exists()) {


                                $GoodsNotes = GoodsNotes::find()->where([
                                            'rest_org_id' => $currentUser->organization_id,
                                            'catalog_base_goods_id' => $CatalogBaseGoods->id
                                        ])->one();
                            } else {
                                $GoodsNotes = new GoodsNotes();
                            }
                            $GoodsNotes->rest_org_id = $currentUser->organization_id;
                            $GoodsNotes->catalog_base_goods_id = $CatalogBaseGoods->id;
                            $GoodsNotes->note = $note;
                            $GoodsNotes->save();
                        } else {
                            if ($GoodsNotes = GoodsNotes::find()->where([
                                        'rest_org_id' => $currentUser->organization_id,
                                        'catalog_base_goods_id' => $CatalogBaseGoods->id
                                    ])->exists()) {

                                $GoodsNotes = GoodsNotes::find()->where([
                                            'rest_org_id' => $currentUser->organization_id,
                                            'catalog_base_goods_id' => $CatalogBaseGoods->id
                                        ])->one();
                                $GoodsNotes->rest_org_id = $currentUser->organization_id;
                                $GoodsNotes->catalog_base_goods_id = $CatalogBaseGoods->id;
                                $GoodsNotes->note = $note;
                                $GoodsNotes->save();
                            }
                        }
                    }

                    if ($base_goods_id) {
                        array_push($array_ids, $base_goods_id);
                    }
                }
                $delete_ids = array_diff($array_base_goods_id, $array_ids);
                if (!empty($delete_ids)) {
                    foreach ($delete_ids as $delete_id) {
                        $CatalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $delete_id])->one();
                        $CatalogBaseGoods->deleted = CatalogBaseGoods::DELETED_ON;
                        $CatalogBaseGoods->save();
                    }
                }
                $transaction->commit();
                $result = ['success' => false, 'alert' => [
                        'class' => 'success-fk',
                        'title' => 'Каталог обновлен',
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
        $catalog = CatalogGoods::find()
                ->joinWith([
                    'baseProduct' => function ($q) {
                        $q->where([
                            CatalogBaseGoods::tableName() . '.deleted' => CatalogBaseGoods::DELETED_OFF]);
                    }, 'goodsNotes'])
                ->where([CatalogGoods::tableName() . '.cat_id' => $catalog_id])
                ->all();


        $array = [];
        foreach ($catalog as $catalog_elem) {
            array_push($array, [
                'catalog_id' => $catalog_elem->cat_id,
                'goods_id' => $catalog_elem->id,
                'base_goods_id' => $catalog_elem->base_goods_id,
                'article' => $catalog_elem->baseProduct->article,
                'product' => $catalog_elem->baseProduct->product,
                'units' => $catalog_elem->baseProduct->units,
                'ed' => $catalog_elem->baseProduct->ed,
                'price' => $catalog_elem->baseProduct->price,
                'note' => isset($catalog_elem->goodsNotes->note) ? $catalog_elem->goodsNotes->note : ''
            ]);
        }
        $array = json_encode($array, JSON_UNESCAPED_UNICODE);
        return $this->renderAjax('suppliers/_editCatalog', compact('id', 'array'));
    }

    public function actionRemoveSupplier() {
        if (Yii::$app->request->isAjax) {
            $id = \Yii::$app->request->post('id');
            $currentUser = User::findIdentity(Yii::$app->user->id);
            RelationSuppRest::deleteAll(['rest_org_id' => $currentUser->organization_id, 'supp_org_id' => $id]);
        }
    }

    public function actionMessages() {
        return $this->render('/site/underConstruction');
    }

    public function actionAnalytics() {
        $currentUser = User::findIdentity(Yii::$app->user->id);

        $header_info_zakaz = \common\models\Order::find()->
                        where(['client_id' => $currentUser->organization_id])->count();
        empty($header_info_zakaz) ? $header_info_zakaz = 0 : $header_info_zakaz = (int) $header_info_zakaz;
        $header_info_suppliers = \common\models\RelationSuppRest::find()->
                        where(['rest_org_id' => $currentUser->organization_id, 'invite' => RelationSuppRest::INVITE_ON])->count();
        empty($header_info_suppliers) ? $header_info_suppliers = 0 : $header_info_suppliers = (int) $header_info_suppliers;
        $header_info_purchases = \common\models\Order::find()->
                        where(['client_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])->count();
        empty($header_info_purchases) ? $header_info_purchases = 0 : $header_info_purchases = (int) $header_info_purchases;
        $header_info_items = \common\models\OrderContent::find()->select('sum(quantity) as quantity')->
                        where(['in', 'order_id', \common\models\Order::find()->select('id')->where(['client_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])])->one()->quantity;
        empty($header_info_items) ? $header_info_items = 0 : $header_info_items = (int) $header_info_items;
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
        $where = " AND `relation_supp_rest`.deleted = 0";
        if (Yii::$app->request->isAjax) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";

            empty($searchString) ? "" : $where .= " and name LIKE :name";
        }
        $sql = "SELECT picture,supp_org_id, name FROM `relation_supp_rest` join `organization`
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
                'pageSize' => 0,
            ],
        ]);

        /*
         * 
         * Поставщики END
         * 
         */
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $orders = $currentUser->organization->getCart();
        $totalCart = count($orders);

        $count_products_from_mp = CatalogBaseGoods::find()
                ->joinWith('vendor')
                ->where([
                    'organization.white_list' => Organization::WHITE_LIST_ON,
                    'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                    'status' => CatalogBaseGoods::STATUS_ON,
                    'deleted' => CatalogBaseGoods::DELETED_OFF])
                ->andWhere('category_id is not null')
                ->count();

        $filter_from_date = date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date = date("d-m-Y");

        //GRIDVIEW ИСТОРИЯ ЗАКАЗОВ ----->
        $searchModel = new \common\models\search\OrderSearch();
        $today = new \DateTime();
        //$searchModel->date_from = date("d.m.Y", strtotime(" -1 months"));
        $searchModel->client_id = $currentUser->organization_id;
        $searchModel->client_search_id = $currentUser->organization_id;

        $dataProvider = $searchModel->search(null);
        $dataProvider->pagination = ['pageSize' => 10];
        // <----- GRIDVIEW ИСТОРИЯ ЗАКАЗОВ

        $organization = $currentUser->organization;
        if ($organization->step == Organization::STEP_SET_INFO) {
            $profile = $currentUser->profile;
            return $this->render('index', compact(
                                    'dataProvider', 'suppliers_dataProvider', 'totalCart', 'count_products_from_mp', 'profile', 'organization'
            ));
        } else {
            return $this->render('index', compact(
                                    'dataProvider', 'suppliers_dataProvider', 'totalCart', 'count_products_from_mp'
            ));
        }
    }

    public function actionSuppliers() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $user = new User();
        $profile = new Profile();
        $relationCategory = new RelationCategory();
        $relationSuppRest = new RelationSuppRest();
        $organization = new Organization();

        $currentOrganization = $this->currentUser->organization;
        $clientName = $this->currentUser->profile->full_name;
        $searchModel = new \common\models\search\VendorSearch();

        $params['VendorSearch'] = Yii::$app->request->post("VendorSearch");

        $dataProvider = $searchModel->search($params, $currentOrganization->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('suppliers', compact('searchModel','clientName', 'dataProvider', 'user', 'organization', 'relationCategory', 'relationSuppRest', 'profile'));
        } else {
            return $this->render('suppliers', compact('searchModel','clientName', 'dataProvider', 'user', 'organization', 'relationCategory', 'relationSuppRest', 'profile'));
        }
    }

    public function actionAddFirstVendor() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $user = new User();
        $profile = new Profile();
        $relationSuppRest = new RelationSuppRest();
        $organization = new Organization();
        
        $relations = RelationSuppRest::find()->where(['rest_org_id' => $currentUser->organization_id, 'deleted' => false])->count();

        $currentOrganization = $this->currentUser->organization;

        $searchModel = new \common\models\search\VendorSearch();

        $params['VendorSearch'] = Yii::$app->request->post("VendorSearch");

        $dataProvider = $searchModel->search($params, $currentOrganization->id);
        $dataProvider->pagination = false;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('add-first-vendor', compact('searchModel', 'dataProvider', 'user', 'organization', 'relationCategory', 'relationSuppRest', 'profile', 'relations'));
        } else {
            return $this->render('add-first-vendor', compact('searchModel', 'dataProvider', 'user', 'organization', 'relationCategory', 'relationSuppRest', 'profile', 'relations'));
        }
    }
    
    public function actionAjaxValidateVendor() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $user =  new User();
        //$user->scenario = 'invite';
        $profile = new Profile();
        $profile->phone = "+7";
        $profile->scenario = 'invite';
        $organization = new Organization();
        $organization->scenario = 'invite';
        $organization->type_id = Organization::TYPE_SUPPLIER;
        
        $post = Yii::$app->request->post();
        if (Yii::$app->request->isAjax && $user->load($post)) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            
            $vendorManager = User::find()
                    ->joinWith('organization')
                    ->where(['user.email' => $user->email, 'organization.type_id' => Organization::TYPE_SUPPLIER])->one();
            if ($vendorManager) {
                $relation = RelationSuppRest::findOne([
                    'supp_org_id' => $vendorManager->organization_id,
                    'rest_org_id' => $currentUser->organization_id,
                    'deleted' => false,
                        ]);
            }
            if ($user->validate() && $vendorManager && empty($relation)) {
                $profile = $vendorManager->profile;
                $organization = $vendorManager->organization;
                $disabled = true;
                return ['errors' => false, 'form' => $this->renderAjax('suppliers/_vendorForm', compact('user', 'profile', 'organization', 'disabled')), 'vendorFound' => true];
//                return ['errors' => false, 'organization_name' => $organization->name, 'phone' => $profile->phone, 'full_name'=>$profile->full_name, 'vendorFound' => true];
            } elseif ($user->validate() && empty($relation)) {
                $validated = true;
                if (!$profile->load($post)) {
                    $profile = new Profile();
                } else {
                    $validated = $profile->validate();
                }
                if ($validated && !$organization->load($post)) {
                    $organization = new Organization();
                } else {
                    $validated = $organization->validate();
                }
                $disabled = false;
                if ($validated) {
                    return ['errors' => false, 'form' => $this->renderAjax('suppliers/_vendorForm', compact('user', 'profile', 'organization', 'disabled')), 'vendorFound' => false];
                    //return ['errors' => false, 'vendorFound' => false];
                }
            }

            return ['errors' => true, 'messages' => \yii\widgets\ActiveForm::validate($user, $profile, $organization), 'vendor_added' => isset($relation)];//\yii\widgets\ActiveForm::validate($user, $profile, $organization);
        }
    }
    
    public function actionSidebar() {
        Yii::$app->session->get('sidebar-collapse') ?
                        Yii::$app->session->set('sidebar-collapse', false) :
                        Yii::$app->session->set('sidebar-collapse', true);
    }

}
