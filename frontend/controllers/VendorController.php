<?php

namespace frontend\controllers;

use common\models\OrderStatus;
use common\models\PaymentSearch;
use common\models\RelationSuppRestPotential;
use common\models\RelationUserOrganization;
use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
use common\models\User;
use common\models\Order;
use common\models\Organization;
use common\models\Role;
use common\models\Profile;
use common\models\search\UserSearch;
use common\models\RelationSuppRest;
use common\models\DeliveryRegions;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\ManagerAssociate;
use common\models\Currency;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\db\Expression;

/**
 * Controller for supplier
 */
class VendorController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
//                'only' => ['index', 'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'tutorial'],
                'rules'      => [
//                    [
//                        
//                    ],
                    [
                        'actions' => [
                            'settings',
                            'delivery',
                            'employees',
                            'ajax-create-user',
                            'ajax-delete-user',
                            'ajax-update-user',
                            'ajax-update-currency',
                            'ajax-validate-user',
                            'remove-client',
                            'payments'
                        ],
                        'allow'   => true,
                        // Allow suppliers managers
                        'roles'   => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                    [
                        'actions' => [
                            'index',
                            'catalog',
                            'tutorial',
                            'ajax-add-client',
                            'ajax-create-product-market-place',
                            'ajax-delete-product',
                            'ajax-invite-rest-org-id',
                            'ajax-set-percent',
                            'ajax-update-product-market-place',
                            'analytics',
                            'basecatalog',
                            'catalogs',
                            'changecatalogprop',
                            'changecatalogstatus',
                            'changesetcatalog',
                            'clients',
                            'create-catalog',
                            'events',
                            'get-sub-cat',
                            'import-base-catalog-from-xls',
                            'import-base-catalog',
                            'import',
                            'import-restaurant',
                            'list-catalog',
                            'messages',
                            'mp-country-list',
                            'mycatalogdelcatalog',
                            'sidebar',
                            'step-1',
                            'step-1-clone',
                            'step-1-update',
                            'step-2',
                            'step-2-add-product',
                            'step-3-copy',
                            'step-3-update-product',
                            'step-4',
                            'supplier-start-catalog-create',
                            'support',
                            'view-catalog',
                            'view-client',
                            'remove-delivery-region',
                            'ajax-change-currency',
                            'ajax-calculate-prices',
                            'chkmail',
                            'ajax-change-main-index',
                            'ajax-delete-main-catalog',
                            'ajax-restore-main-catalog-latest-snapshot',
                        ],
                        'allow'   => true,
                        // Allow suppliers managers
                        'roles'   => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//            $this->redirect(Url::to(['/vendor/index']));
//        }
            ],
        ];
    }

    /*
     *  Main settings page
     */

    public function actionSettings()
    {
        $organization           = $this->currentUser->organization;
        $organization->scenario = "settings";
        $post                   = Yii::$app->request->post();
        if ($organization->load(Yii::$app->request->post())) {
            if ($organization->validate()) {
                $organization->address = $organization->formatted_address;
                if (!$post['Organization']['is_allowed_for_franchisee']) {
                    User::updateAll(['organization_id' => null], ['organization_id' => $organization->id, 'role_id' => Role::getFranchiseeEditorRoles()]);
                }
                if ($organization->step == Organization::STEP_SET_INFO) {
                    $organization->step = Organization::STEP_ADD_CATALOG;
                    $organization->save();
                    return $this->redirect(['vendor/catalogs']);
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

    public function actionEmployees(): String
    {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel                             = new UserSearch();
        $params['UserSearch']                    = Yii::$app->request->post("UserSearch");
        $this->loadCurrentUser();
        $organizationId                          = $this->currentUser->organization_id;
        $params['UserSearch']['organization_id'] = $organizationId;
        $dataProvider                            = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('employees', compact('searchModel', 'dataProvider', 'organizationId'));
        } else {
            return $this->render('employees', compact('searchModel', 'dataProvider', 'organizationId'));
        }
    }

    public function actionDelivery()
    {
        $organization                 = $this->currentUser->organization;
        $supplier                     = $organization->id;
        $regionsList                  = DeliveryRegions::find()->where(['supplier_id' => $supplier])->all();
        $deliveryRegions              = new DeliveryRegions();
        $deliveryRegions->supplier_id = $supplier;

        $delivery = $organization->delivery;

        if (!$delivery) {
            $delivery            = new \common\models\Delivery();
            $delivery->vendor_id = $organization->id;
            $delivery->save();
        }

        if ($deliveryRegions->load(Yii::$app->request->post()) && $deliveryRegions->validate()) {
            $deliveryRegions->save();
        }

        if ($delivery->load(Yii::$app->request->get())) {
            if ($delivery->validate()) {
                $delivery->save();
            }
        }
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('delivery', compact('delivery', 'regionsList', 'supplier', 'deliveryRegions'));
        } else {
            return $this->render('delivery', compact('delivery', 'regionsList', 'supplier', 'deliveryRegions'));
        }
    }

    public function actionRemoveDeliveryRegion(int $id): void
    {
        $organization    = $this->currentUser->organization;
        $deliveryRegions = \common\models\DeliveryRegions::findOne($id);
        if ($deliveryRegions) {
            $deliveryRegions->delete();
        }
    }

    /*
     *  User validate
     */

    public function actionAjaxValidateUser()
    {
        $user    = new User(['scenario' => 'manageNew']);
        $profile = new Profile();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                //if ($user->validate() && $profile->validate()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return json_encode(\yii\widgets\ActiveForm::validateMultiple([$user, $profile]));
                //} 
            }
        }
    }

    /*
     *  User create
     */

    public function actionAjaxCreateUser()
    {
        $user             = new User(['scenario' => 'manageNew']);
        $profile          = new Profile();
        $this->loadCurrentUser();
        $organizationType = $this->currentUser->organization->type_id;
        $dropDown         = Role::dropdown($organizationType);
        $selected         = null;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {
                    if (!in_array($user->role_id, User::getAllowedRoles($this->currentUser->role_id)) && $this->currentUser->role_id != Role::ROLE_FRANCHISEE_OWNER) {
                        $user->role_id = array_keys($dropDown)[0];
                    }

                    $user->setRegisterAttributes($user->role_id, $user->status)->save();
                    //$profile->email = $user->getEmail();
                    $profile->setUser($user->id)->save();
                    $user->setOrganization($this->currentUser->organization, false, true)->save();
                    $user->wipeNotifications();
                    $this->currentUser->sendEmployeeConfirmation($user);
                    $user->setRelationUserOrganization($user->organization->id, $user->role_id);

                    $message = Yii::t('app', 'Пользователь добавлен!');
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                } else {
                    if (array_key_exists('email', $user->errors)) {
                        $existingUser = User::findOne(['email' => $post['User']['email']]);
                        if (in_array($existingUser->role_id, Role::getAdminRoles()) || in_array($existingUser->role_id, Role::getFranchiseeEditorRoles())) {
                            $newRole = $existingUser->role_id;
                        } else {
                            $newRole = $post['User']['role_id'];
                        }
                        $success = $existingUser->setRelationUserOrganization($this->currentUser->organization->id, $newRole);
                        if ($success) {

                            //$existingUser->setOrganization($this->currentUser->organization, false, true)->save();
                            //$existingUser->setRole($post['User']['role_id'])->save();
                            $message = Yii::t('app', 'Пользователь добавлен!');
                        } else {
                            $message = Yii::t('app', 'common.models.already_exists');
                        }

                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'dropDown', 'selected'));
    }

    /*
     *  User update
     */

    public function actionAjaxUpdateUser(int $id): String
    {
        $user                      = User::findIdentity($id);
        $user->setScenario("manage");
        $oldRole                   = $user->role_id;
        $profile                   = $user->profile;
        $currentUserOrganizationID = $this->currentUser->organization_id;
        $dropDown                  = Role::dropdown(Role::getRelationOrganizationType($id, $currentUserOrganizationID));
        $selected                  = $user->getRelationUserOrganizationRoleID($currentUserOrganizationID);

        if (in_array($user->role_id, Role::getAdminRoles()) || in_array($user->role_id, Role::getFranchiseeEditorRoles())) {
            return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'dropDown', 'selected'));
        }

        if (Yii::$app->request->isAjax) {
            $post  = Yii::$app->request->post();
            $email = $user->email;
            if ($user->load($post) && !in_array($user->role_id, Role::getAdminRoles())) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    if (!in_array($user->role_id, User::getAllowedRoles($oldRole))) {
                        $user->role_id = $oldRole;
                    } elseif ($user->role_id == Role::ROLE_SUPPLIER_EMPLOYEE && $oldRole == Role::ROLE_SUPPLIER_MANAGER && $user->organization->managersCount == 1) {
                        $user->role_id = $oldRole;
                    }
                    $user->email = $email;
                    $user->save();
                    //$profile->email = $user->getEmail();
                    $profile->save();
                    $user->updateRelationUserOrganization($currentUserOrganizationID, $post['User']['role_id']);

                    $message = Yii::t('app', 'Пользователь обновлен!');
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                } else {
                    $profile->validate();
                }
            }
        }

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'dropDown', 'selected'));
    }

    public function actionCatalogs()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);

        if (!Catalog::find()->where(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
            $step = $currentUser->organization->step;
            return $this->render("catalogs/createBaseCatalog", compact("Catalog", "step"));
        } else {
            $currentOrganization = $currentUser->organization;
            if ($currentOrganization->step == Organization::STEP_ADD_CATALOG) {
                $currentOrganization->step = Organization::STEP_OK;
                $currentOrganization->save();
            }
            $searchString       = "";
            $restaurant         = "";
            $type               = "";
            $relation_supp_rest = new RelationSuppRest;
            $relation           = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                    where(['in', 'id', \common\models\RelationSuppRest::find()->
                                        select('rest_org_id')->
                                        where(['supp_org_id' => $currentUser->organization_id, 'invite' => '1', 'deleted' => false])])->all(), 'id', 'name');
            $arrCatalog         = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'currency_id'])->
                            where(['supp_org_id' => $currentUser->organization_id, 'type' => 2])->all();

            if (Yii::$app->request->isPost) {
                $searchString = htmlspecialchars(trim(\Yii::$app->request->post('searchString')));
                $restaurant   = htmlspecialchars(trim(\Yii::$app->request->post('restaurant')));
                //echo $restaurant;
                if (!empty($restaurant)) {
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'type', 'id'])->
                                    where(['supp_org_id' => $currentUser->organization_id])->
                                    andFilterWhere(['id' => \common\models\RelationSuppRest::find()->
                                        select(['cat_id'])->
                                        where(['supp_org_id' => $currentUser->organization_id,
                                            'rest_org_id' => $restaurant, 'deleted'     => false])])->one();
                    if (empty($arrCatalog)) {
                        $arrCatalog == "";
                    } else {
                        if ($arrCatalog->type == 1) {
                            $type = 1;  //ресторан подключен к главному каталогу
                        } else {
                            $catalog_id = $arrCatalog->id;
                            $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
                                            where(['supp_org_id' => $currentUser->organization_id, 'id' => $catalog_id])->all();
                        }
                    }
                } else {
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'currency_id'])->
                                    where(['supp_org_id' => $currentUser->organization_id, 'type' => 2])->
                                    andFilterWhere(['LIKE', 'name', $searchString])->all();
                }
            } else {
                $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'currency_id'])->
                                where(['supp_org_id' => $currentUser->organization_id, 'type' => 2])->
                                andFilterWhere(['LIKE', 'name', $searchString])->all();
            }
            return $this->render("catalogs", compact("relation_supp_rest", "currentUser", "relation", "searchString", "restaurant", 'arrCatalog', 'type'));
        }
        return $this->render("catalogs", compact("relation_supp_rest", "currentUser", "relation", "searchString", "restaurant", 'type', 'arrCatalog'));
        //}
    }

    public function actionSupplierStartCatalogCreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser                = User::findIdentity(Yii::$app->user->id);

            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            if ($arrCatalog === []) {
                $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('app', 'УПС! Ошибка'), 'body' => Yii::t('app', 'Нельзя сохранить пустой каталог!')]];
                return $result;
            }

            //проверка на корректность введенных данных (цена)
            $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            $arrEd         = \common\models\MpEd::dropdown();
            $productArray  = [];
            foreach ($arrCatalog as $arrCatalogs) {
                $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                $units   = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                $price   = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                $ed      = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
                $note    = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                if (in_array($product, $productArray)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_three', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'Вы пытаетесь загрузить одну или более позиций с одинаковым наименованием!')]];
                    return $result;
                }
                array_push($productArray, (string) $product);
                if (empty($product)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_three', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.empty_name', ['ru' => 'Не указано <strong>Наименование</strong>'])]];
                    return $result;
                }
                if (empty($price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_four', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.empty_price', ['ru' => 'Не указана <strong>Цена</strong> продукта'])]];
                    return $result;
                }
                if (empty($ed)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_five', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.empty_ed', ['ru' => 'Не указана <strong>Единица измерения</strong> товара'])]];
                    return $result;
                }
                if (!in_array($ed, $arrEd)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_six', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.wrong_ed', ['ru' => 'Неверная <strong>Единица измерения</strong> товара'])]];
                    return $result;
                }
                $price = str_replace(',', '.', $price);

                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_seven', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.wrong_price', ['ru' => 'Неверный формат <strong>Цены</strong><br><small>только число в формате 0,00</small>'])]];
                    return $result;
                }
                if (!empty($units) && !preg_match($numberPattern, $units)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_eight', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.wrong_number', ['ru' => 'Неверный формат <strong>Кратность</strong><br><small>только число</small>'])]];
                    return $result;
                }
            }

            $currency  = Currency::findOne(['id' => Yii::$app->request->post('currency')]);
            $mainIndex = Yii::$app->request->post('main_index');

            $newBaseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
            if (empty($newBaseCatalog)) {
                $newBaseCatalog              = new Catalog();
                $newBaseCatalog->supp_org_id = $currentUser->organization_id;
                $newBaseCatalog->name        = Yii::t('app', 'Главный каталог');
                $newBaseCatalog->type        = Catalog::BASE_CATALOG;
                $newBaseCatalog->status      = Catalog::STATUS_ON;
            }
            if (!empty($currency)) {
                $newBaseCatalog->currency_id = $currency->id;
            }
            if (Catalog::isMainIndexValid($mainIndex)) {
                $newBaseCatalog->main_index = $mainIndex;
            }
            $newBaseCatalog->save();

            $lastInsert_base_cat_id = $newBaseCatalog->id;

            foreach ($arrCatalog as $arrCatalogs) {
                $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                $units   = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                if (empty($units)) {
                    $units = 0;
                }
                $ed   = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
                $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));

                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                $price = str_replace(',', '.', $price);
                if (substr($price, -3, 1) == '.') {
                    $price = explode('.', $price);
                    $last  = array_pop($price);
                    $price = join($price, '') . '.' . $last;
                } else {
                    $price = str_replace('.', '', $price);
                }

                $sql                      = "insert into {{%catalog_base_goods}}" .
                    "(cat_id,supp_org_id,article,product,"
                    . "units,price,category_id,note,ed,status,market_place,deleted,created_at) VALUES ("
                        . $lastInsert_base_cat_id . ","
                        . $currentUser->organization_id . ","
                        . ":article,"
                        . ":product,"
                        . ":units,"
                        . ":price,"
                        . "NULL,"
                        . ":note,"
                        . ":ed,"
                        . CatalogBaseGoods::STATUS_ON . ","
                        . "0,"
                        . "0,"
                        . "NOW())";
                $command                  = \Yii::$app->db->createCommand($sql);
                $command->bindParam(":article", $article, \PDO::PARAM_STR);
                $command->bindParam(":product", $product, \PDO::PARAM_STR);
                $command->bindParam(":units", $units);
                $command->bindParam(":price", $price);
                $command->bindParam(":note", $note, \PDO::PARAM_STR);
                $command->bindParam(":ed", $ed, \PDO::PARAM_STR);
                $command->execute();
                $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();
            }
            $result              = ['success' => true, 'alert' => ['class' => 'success-fk', 'title' => Yii::t('message', 'frontend.controllers.vendor.congr', ['ru' => 'Поздравляем!']), 'body' => Yii::t('message', 'frontend.controllers.vendor.cat_cr', ['ru' => 'Вы успешно создали свой первый каталог!'])]];
            $currentOrganization = $currentUser->organization;
            if ($currentOrganization->step == Organization::STEP_ADD_CATALOG) {
                $currentOrganization->step = Organization::STEP_OK;
                $currentOrganization->save();
            }
            return $result;
        }
    }

    public function actionClients()
    {
        $currentOrganization = User::findIdentity(Yii::$app->user->id)->organization;
        $searchModel         = new \common\models\search\ClientSearch();

        $params['ClientSearch'] = Yii::$app->request->post("ClientSearch");

        $dataProvider = $searchModel->search($params, $currentOrganization->id, Yii::$app->user->can('manage') ? null : $this->currentUser->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('clients', compact('searchModel', 'dataProvider', 'currentOrganization'));
        } else {
            return $this->render('clients', compact('searchModel', 'dataProvider', 'currentOrganization'));
        }
    }

    public function actionRemoveClient()
    {
        if (Yii::$app->request->isAjax) {
            $id          = \Yii::$app->request->post('id');
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $sql         = "delete from relation_supp_rest where supp_org_id =$currentUser->organization_id and rest_org_id = $id";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public function actionBasecatalog()
    {
        $sort = \Yii::$app->request->get('sort') ?? '';

        $currentUser  = User::findIdentity(Yii::$app->user->id);
        $searchString = "";
        $baseCatalog  = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($baseCatalog)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $currentCatalog = $baseCatalog;

        $dataProvider = CatalogBaseGoods::getDataForExcelExport($baseCatalog, $sort, true);

        $searchModel2  = new RelationSuppRest;
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        $cat_id        = $baseCatalog->id;
        return $this->render('catalogs/basecatalog', compact('searchString', 'dataProvider', 'searchModel2', 'dataProvider2', 'currentCatalog', 'cat_id', 'currentUser'));
    }

    public function actionImport($id)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
        $vendor      = \common\models\Catalog::find()->where([
                            'id'   => $id,
                            'type' => \common\models\Catalog::BASE_CATALOG
                        ])
                        ->one()
                ->vendor;
        if (Yii::$app->request->isPost) {
            $catalog                 = Catalog::findOne(['id' => $id]);
            $catalog->makeSnapshot();
            $importType              = \Yii::$app->request->post('UploadForm')['importType'];
            //$unique = 'product'; //уникальное поле
            $sql_array_products      = CatalogBaseGoods::find()->select(['id', 'product'])->where(['cat_id' => $id, 'deleted' => 0])->asArray()->all();
            $arr                     = \yii\helpers\ArrayHelper::map($sql_array_products, 'id', 'product');
            unset($sql_array_products);
            //$count_array = count($sql_array_products);
            $arr                     = array_map('mb_strtolower', $arr);
            //массив уникального поля из базы
//            if (!empty($sql_array_products)) {
//                for ($i = 0; $i < $count_array; $i++) {
//                    array_push($arr, strtolower(trim($sql_array_products[$i][$unique])));
//                }
//            }
            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path                    = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.cat_error', ['ru' => 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'])
                        . Yii::t('error', 'frontend.controllers.vendor.error_repeat', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $localFile     = \PHPExcel_IOFactory::identify($path);
            $objReader     = \PHPExcel_IOFactory::createReader($localFile);
            //Память для Кэширования
            $cacheMethod   = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = ['memoryCacheSize ' => '64MB'];
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            //Оптимизируем чтение файла
            $objReader->setReadDataOnly(true);
            $objPHPExcel   = $objReader->load($path);
            $worksheet     = $objPHPExcel->getSheet(0);

            unset($objPHPExcel);
            unset($objReader);

            $highestRow = $worksheet->getHighestRow(); // получаем количество строк
            $xlsArray   = [];

            if ($highestRow > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                        . Yii::t('app', '<small>Вы пытаетесь загрузить каталог объемом больше ') . CatalogBaseGoods::MAX_INSERT_FROM_XLS . Yii::t('app', ' позиций, обратитесь к нам и мы вам поможем')
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            //Проверяем наличие дублей в списке
            if ($importType == 2 || $importType == 3) {
                $rP = 0;
            } else {
                $rP = 1;
            }
            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                array_push($xlsArray, mb_strtolower(trim($worksheet->getCellByColumnAndRow($rP, $row))));
            }
            if (count($xlsArray) !== count(array_flip($xlsArray))) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                        . Yii::t('app', '<small>Вы пытаетесь загрузить одну или более позиций с одинаковым наименованием! Проверьте файл на наличие дублей! ')
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            unset($xlsArray);

            if ($importType == 1) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $data_insert = [];
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_article = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //артикул
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(1, $row))); //наименование
                        $row_units   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                        $row_price   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                        $row_ed      = strip_tags(trim(str_replace('.', ',', $worksheet->getCellByColumnAndRow(4, $row)))); //единица измерения
                        $row_note    = strip_tags(trim($worksheet->getCellByColumnAndRow(5, $row)));  //Комментарий
                        if (!empty($row_product && $row_price && $row_ed)) {
                            if (empty($row_units) || $row_units < 0) {
                                $row_units = 0;
                            }
                            if (!in_array(mb_strtolower($row_product), $arr)) {
                                $new_item              = new CatalogBaseGoods;
                                $new_item->cat_id      = $id;
                                $new_item->supp_org_id = $vendor->id;
                                $new_item->article     = $row_article;
                                $new_item->product     = $row_product;
                                $new_item->units       = $row_units;
                                $new_item->price       = $row_price;
                                $new_item->ed          = $row_ed;
                                $new_item->note        = $row_note;
                                $new_item->status      = CatalogBaseGoods::STATUS_ON;
                                $new_item->save();
                                /* $data_insert[] = [
                                  $id,
                                  $vendor->id,
                                  $row_article,
                                  $row_product,
                                  $row_units,
                                  $row_price,
                                  $row_ed,
                                  $row_note,
                                  CatalogBaseGoods::STATUS_ON
                                  ]; */
                            }
                        }
                    }
                    unset($worksheet);
                    /* if (!empty($data_insert)) {
                      $db          = Yii::$app->db;
                      $data_chunks = array_chunk($data_insert, 1000);
                      unset($data_insert);
                      foreach ($data_chunks as $data_insert) {
                      $sql = $db->queryBuilder->batchInsert(CatalogBaseGoods::tableName(), [
                      'cat_id', 'supp_org_id', 'article', 'product', 'units', 'price', 'ed', 'note', 'status'
                      ], $data_insert);
                      Yii::$app->db->createCommand($sql)->execute();
                      }
                      } */
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(['vendor/basecatalog', 'id' => $id]);
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error', ['ru' => 'Ошибка сохранения, повторите действие'])
                            . Yii::t('error', 'frontend.controllers.vendor.saving_error_two', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                            . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                }
            }
            if ($importType == 2) {
                $data_update = "";
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $cbgTable = CatalogBaseGoods::tableName();
                    $batch    = 0;
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                        $row_price   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(1, $row))); //цена
                        if (!empty($row_product && $row_price)) {
                            if (empty($row_units) || $row_units < 0) {
                                $row_units = 0;
                            }
                            $cbg_id = array_search(mb_strtolower($row_product), $arr);
                            if ($cbg_id) {
                                if ($batch < 1000) {
                                    $data_update .= "UPDATE $cbgTable set price = $row_price where cat_id=$id and id=$cbg_id;";
                                    $batch++;
                                } else {
                                    Yii::$app->db->createCommand($data_update)->execute();
                                    $data_update = "UPDATE $cbgTable set price = $row_price where cat_id=$id and id=$cbg_id;";
                                    $batch       = 0;
                                }
                            }
                        }
                    }
                    if (!empty($data_update)) {
                        Yii::$app->db->createCommand($data_update)->execute();
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(['vendor/basecatalog', 'id' => $id]);
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_three', ['ru' => 'Ошибка сохранения, повторите действие'])
                            . Yii::t('error', 'frontend.controllers.vendor.saving_error_four', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                            . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                }
            }
            if ($importType == 3) {
                $data_update = "";
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $cbgTable = CatalogBaseGoods::tableName();
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                        if (!empty($row_product)) {
                            if (empty($row_units) || $row_units < 0) {
                                $row_units = 0;
                            }
                            $cbg_id = array_search(mb_strtolower($row_product), $arr);
                            if ($cbg_id) {
                                $data_update .= "UPDATE $cbgTable set 
                                    market_place = 1,
                                    mp_show_price = 1,
                                    es_status = 1
                                     where cat_id=$id and id='$cbg_id'"
                                    . " and ed is not null and category_id is not null;";
                            }
                        }
                    }
                    if (!empty($data_update)) {
                        Yii::$app->db->createCommand($data_update)->execute();
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(['vendor/basecatalog', 'id' => $id]);
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_five', ['ru' => 'Ошибка сохранения, повторите действие'])
                            . Yii::t('error', 'frontend.controllers.vendor.repeat_error', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                            . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                }
            }
        }
        return $this->renderAjax('catalogs/_importForm', compact('importModel'));
    }

    public function actionImportRestaurant($id)
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
        $baseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->id, 'type' => Catalog::BASE_CATALOG]);
        if (Yii::$app->request->isPost) {
            $importType         = \Yii::$app->request->post('UploadForm')['importType'];
            $unique             = 'product'; //уникальное поле
            $sql_array_products = [];
            if ($importType == 1) {
                $sql_array_products = CatalogBaseGoods::find()->select(['id', 'product'])->where(['supp_org_id' => $currentUser->organization->id, 'deleted' => 0])->asArray()->all();
            }
            if ($importType == 2) {
                $sql_array_products = CatalogGoods::find()
                                ->select(['catalog_base_goods.id', 'catalog_base_goods.product'])
                                ->joinWith('baseProduct', false)
                                ->where([
                                    'catalog_base_goods.supp_org_id' => $currentUser->organization->id, 'catalog_goods.cat_id'           => $id])->asArray()->all();
            }
            $arr = array_map('mb_strtolower', \yii\helpers\ArrayHelper::map($sql_array_products, 'id', 'product'));
            unset($sql_array_products);

            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path                    = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.file_error', ['ru' => 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'])
                        . Yii::t('error', 'frontend.controllers.vendor.error_repeat_two', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $localFile   = \PHPExcel_IOFactory::identify($path);
            $objReader   = \PHPExcel_IOFactory::createReader($localFile);
            $objPHPExcel = $objReader->load($path);
            $worksheet   = $objPHPExcel->getSheet(0);

            unset($objPHPExcel);
            unset($objReader);

            $highestRow    = $worksheet->getHighestRow(); // получаем количество строк
            $highestColumn = $worksheet->getHighestColumn(); // а так можно получить количество колонок
            $newRows       = 0;
            $xlsArray      = [];

            if ($highestRow > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                        . Yii::t('app', '<small>Вы пытаетесь загрузить каталог объемом больше ') . CatalogBaseGoods::MAX_INSERT_FROM_XLS . Yii::t('app', ' позиций, обратитесь к нам и мы вам поможем')
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }

            //Проверяем наличие дублей в списке
            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                array_push($xlsArray, mb_strtolower(trim($worksheet->getCellByColumnAndRow(0, $row))));
            }

            if (count($xlsArray) !== count(array_flip($xlsArray))) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                        . Yii::t('app', '<small>Вы пытаетесь загрузить одну или более позиций с одинаковым наименованием! Проверьте файл на наличие дублей! ')
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }

            $flipArr = array_flip($arr);

            if ($importType == 1) {
                $transaction  = Yii::$app->db->beginTransaction();
                $catalogGoods = CatalogGoods::find()
                                ->select(['catalog_goods.id', 'catalog_goods.base_goods_id as cbg_id'])
                                ->joinWith('baseProduct', false)
                                ->where([
                                    'catalog_base_goods.supp_org_id' => $currentUser->organization->id, 'catalog_goods.cat_id'           => $id])->asArray()->all();
                $catalogGoods = \yii\helpers\ArrayHelper::map($catalogGoods, 'cbg_id', 'id');
                try {
                    $data_insert = [];
                    for ($row = 1; $row <= $highestRow; ++$row) {
                        $row_product = strip_tags(mb_strtolower(trim($worksheet->getCellByColumnAndRow(0, $row)))); //наименование
                        $row_price   = strip_tags(floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(1, $row)))); //цена

                        if (!empty($row_product && $row_price)) {

                            $cbg_id = isset($flipArr[mb_strtolower($row_product)]) ? $flipArr[mb_strtolower($row_product)] : null;
                            if ($cbg_id) {
                                //$checkExisting = CatalogGoods::find()->where(['base_goods_id' => $cbg_id, 'cat_id' => $id])->exists();
                                if (!isset($catalogGoods[$cbg_id])) {
                                    $data_insert[] = [
                                        $id,
                                        $cbg_id,
                                        $row_price
                                    ];
                                }
                            }
                        }
                    }
                    unset($worksheet);
                    if (!empty($data_insert)) {
                        $db          = Yii::$app->db;
                        $data_chunks = array_chunk($data_insert, 1000);
                        unset($data_insert);
                        foreach ($data_chunks as $data_insert) {
                            $sql = $db->queryBuilder->batchInsert(CatalogGoods::tableName(), [
                                'cat_id', 'base_goods_id', 'price'
                                    ], $data_insert);
                            Yii::$app->db->createCommand($sql)->execute();
                        }
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(['vendor/step-3-copy', 'id' => $id]);
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_six', ['ru' => 'Ошибка сохранения, повторите действие'])
                            . Yii::t('error', 'frontend.controllers.vendor.error_repeat_three', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                            . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                }
            }
            if ($importType == 2) {
                $data_update = "";
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $cgTable = CatalogGoods::tableName();
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                        $row_price   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(1, $row))); //цена
                        if (!empty($row_product && $row_price)) {
                            $cbg_id = array_search(mb_strtolower($row_product), $arr);
                            if ($cbg_id) {
                                $data_update .= "UPDATE $cgTable set 
                                        price = $row_price
                                         where cat_id=$id and base_goods_id=$cbg_id;";
                            }
                        }
                    }
                    if (!empty($data_update)) {
                        Yii::$app->db->createCommand($data_update)->execute();
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(['vendor/step-3-copy', 'id' => $id]);
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_seven', ['ru' => 'Ошибка сохранения, повторите действие'])
                            . Yii::t('error', 'frontend.controllers.vendor.error_repeat_four', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                            . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                }
            }
        }
        return $this->renderAjax('catalogs/_importFormRestaurant', compact('importModel'));
    }

    public function actionImportBaseCatalogFromXls()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            $catalog = Catalog::findOne(['supp_org_id' => $currentUser->organization->id]);
            if (!$catalog) {
                $catalog = new Catalog();
            }
            $catalog->makeSnapshot();
            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path                    = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_eight', ['ru' => 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'])
                        . Yii::t('error', 'frontend.controllers.vendor.error_repeat_five', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $localFile   = \PHPExcel_IOFactory::identify($path);
            $objReader   = \PHPExcel_IOFactory::createReader($localFile);
            $objPHPExcel = $objReader->load($path);
            $worksheet   = $objPHPExcel->getSheet(0);

            unset($objReader);
            unset($objPHPExcel);

            $highestRow    = $worksheet->getHighestRow(); // получаем количество строк
            $highestColumn = $worksheet->getHighestColumn(); // а так можно получить количество колонок

            if ($highestRow > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.cat_error_ten', ['ru' => 'Ошибка загрузки каталога<br>'])
                        . Yii::t('error', 'frontend.controllers.', ['ru' => '<small>Вы пытаетесь загрузить каталог объемом больше {max} позиций (Новых позиций), обратитесь к нам и мы вам поможем', 'max' => CatalogBaseGoods::MAX_INSERT_FROM_XLS])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $xlsArray = [];
            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                $row_product = trim($worksheet->getCellByColumnAndRow(1, $row)); //наименование
                array_push($xlsArray, $row_product);
            }
            if (count($xlsArray) !== count(array_flip($xlsArray))) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                        . Yii::t('app', '<small>Вы пытаетесь загрузить один или более позиций с одинаковым названием! Проверьте файл на наличие дублей! ')
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            unset($xlsArray);
            $transaction = Yii::$app->db->beginTransaction();
            try {

                $sql = "insert into " . Catalog::tableName() . "(supp_org_id,name,type,created_at,status) VALUES ($currentUser->organization_id,'Главный каталог'," . Catalog::BASE_CATALOG . ",NOW(),1)";
                \Yii::$app->db->createCommand($sql)->execute();
                $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();

                $batch    = 0;
                $batchNum = 0;
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_article = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //артикул
                    $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(1, $row))); //наименование
                    $row_units   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                    $row_price   = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                    $row_ed      = strip_tags(trim($worksheet->getCellByColumnAndRow(4, $row))); //единица измерения
                    $row_note    = strip_tags(trim($worksheet->getCellByColumnAndRow(5, $row))); //коммент
                    if (!empty($row_product && $row_price && $row_ed)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        $new_item              = new CatalogBaseGoods;
                        $new_item->cat_id      = $lastInsert_base_cat_id;
                        $new_item->supp_org_id = $currentUser->organization_id;
                        $new_item->article     = $row_article;
                        $new_item->product     = $row_product;
                        $new_item->units       = $row_units;
                        $new_item->price       = $row_price;
                        $new_item->ed          = $row_ed;
                        $new_item->note        = $row_note;
                        $new_item->status      = CatalogBaseGoods::STATUS_ON;
                        $new_item->save();
                        /* $data_chunks[$batchNum][] = [
                          $lastInsert_base_cat_id,
                          $currentUser->organization_id,
                          $row_article,
                          $row_product,
                          $row_units,
                          $row_price,
                          $row_ed,
                          $row_note,
                          CatalogBaseGoods::STATUS_ON,
                          new \yii\db\Expression('NOW()'),
                          ]; */
                        $batch++;
                        if ($batch === 1000) {
                            $batch = 0;
                            $batchNum++;
                        }
                    }
                }
                unset($worksheet);
                /* if (!empty($data_chunks)) {
                  for ($chunk = 0; $chunk < count($data_chunks); ++$chunk) {
                  $db                  = Yii::$app->db;
                  $sql                 = $db->queryBuilder->batchInsert(CatalogBaseGoods::tableName(), [
                  'cat_id', 'supp_org_id', 'article', 'product', 'units', 'price', 'ed', 'note', 'status', 'created_at'
                  ], $data_chunks[$chunk]);
                  Yii::$app->db->createCommand($sql)->execute();
                  $data_chunks[$chunk] = [];
                  }
                  } */
                $transaction->commit();
                unlink($path);
                return $this->redirect(['vendor/basecatalog', 'id' => $lastInsert_base_cat_id]);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_nine', ['ru' => 'Ошибка сохранения, повторите действие'])
                        . Yii::t('error', 'frontend.controllers.vendor.error_repeat_six', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            }
        }
        return $this->renderAjax('catalogs/_importCreateBaseForm', compact('importModel'));
    }

    public function actionChangestatus()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id                         = \Yii::$app->request->post('id');
            $status                     = \Yii::$app->request->post('status');
            $status == 1 ? $status                     = 0 : $status                     = 1;
            $catalog                    = Catalog::findOne(['id' => $id, 'type' => Catalog::CATALOG]);
            if (isset($catalog)) {
                $catalog->status = $status;
                $catalog->update();
            }
            $result = ['success' => true, 'status' => $status];
            return $result;
        }
    }

    public function actionAjaxInviteRestOrgId()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser                = User::findIdentity(Yii::$app->user->id);
            $relationSuppRest           = new RelationSuppRest;

            $id    = \Yii::$app->request->post('id');
            $elem  = \Yii::$app->request->post('elem');
            $state = \Yii::$app->request->post('state');
            if ($elem == 'restOrgId') {
                if ($state == 'true') {
                    $relationSuppRest         = RelationSuppRest::findOne(['rest_org_id' => $id, 'supp_org_id' => $currentUser->organization_id]);
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    $relationSuppRest->save();

                    $result = ['success' => true, 'status' => 'update invite'];
                    return $result;
                } else {
                    $relationSuppRest         = RelationSuppRest::findOne(['rest_org_id' => $id, 'supp_org_id' => $currentUser->organization_id]);
                    $relationSuppRest->invite = RelationSuppRest::INVITE_OFF;
                    $relationSuppRest->cat_id = RelationSuppRest::CATALOG_STATUS_OFF;
                    $relationSuppRest->save();

                    $result = ['success' => true, 'status' => 'no update invite'];
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

            $Catalog = Catalog::find()->where(['id' => $cat_id, 'type' => 2])->one();
            $Catalog->delete();

            $CatalogGoods = CatalogGoods::deleteAll(['cat_id' => $cat_id]);

            $RelationSuppRest = RelationSuppRest::updateAll(['cat_id' => 0], ['cat_id' => $cat_id]);

            $result = ['success' => true];
            return $result;
        }
    }

    public function actionAjaxDeleteProduct()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id       = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll(['deleted' => 1, 'es_status' => 2], ['id' => $product_id]);

            $result = ['success' => true];
            return $result;
        }
    }

    public function actionAjaxCreateProductMarketPlace()
    {
        $currentUser      = User::findIdentity(Yii::$app->user->id);
        $catalogBaseGoods = new CatalogBaseGoods(['scenario' => 'marketPlace']);
        $sql              = "SELECT id, name FROM mp_country WHERE name = \"Россия\"
	UNION SELECT id, name FROM mp_country WHERE name <> \"Россия\"";
        $countrys         = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($countrys as &$country) {
            $country['name'] = Yii::t('app', $country['name']);
        }
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                $checkBaseGood = CatalogBaseGoods::findAll(['cat_id' => $catalogBaseGoods->cat_id, 'product' => $catalogBaseGoods->product, 'deleted' => 0]);
                if ($checkBaseGood) {
                    $message = Yii::t('error', 'frontend.controllers.vendor.cat_error_five_two');
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
                $catalogBaseGoods->status      = 1;
                $catalogBaseGoods->price       = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                $catalogBaseGoods->supp_org_id = $currentUser->organization_id;

                if ($catalogBaseGoods->market_place == 1) {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status   = 1;
                        $catalogBaseGoods->save();
                        $message                       = Yii::t('app', 'Товар добавлен!');
                        return $this->renderAjax('catalogs/_success', ['message' => $message]);
                    }
                } else {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id  = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->market_place = 0;
                        $catalogBaseGoods->save();
                        $message                        = Yii::t('app', 'Товар добавлен!');
                        return $this->renderAjax('catalogs/_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('catalogs/_baseProductMarketPlaceForm', compact('catalogBaseGoods', 'countrys'));
    }

    public function actionAjaxUpdateProductMarketPlace($id)
    {
        $currentUser                = User::findIdentity(Yii::$app->user->id);
        $catalogBaseGoods           = CatalogBaseGoods::find()->where(['id' => $id])->one();
        $catalogBaseGoods->scenario = 'marketPlace';
        $sql                        = "SELECT id, name FROM mp_country WHERE name = \"Россия\"
	UNION SELECT id, name FROM mp_country WHERE name <> \"Россия\"";
        $countrys                   = \Yii::$app->db->createCommand($sql)->queryAll();

        if (!empty($catalogBaseGoods->category_id)) {
            $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
            $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
        }

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if (isset($post['CatalogBaseGoods']['units']) && strpos($post['CatalogBaseGoods']['units'], ',')) {
                $post['CatalogBaseGoods']['units'] = str_replace(',', '.', $post['CatalogBaseGoods']['units']);
            }
            if ($catalogBaseGoods->load($post)) {
                $checkBaseGood = CatalogBaseGoods::find()->where(['cat_id' => $catalogBaseGoods->cat_id, 'product' => $catalogBaseGoods->product, 'deleted' => 0])->andWhere(['<>', 'id', $id])->all();
                if (count($checkBaseGood)) {
                    $message = Yii::t('error', 'frontend.controllers.vendor.cat_error_five_two');
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
                $catalogBaseGoods->price       = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                $catalogBaseGoods->supp_org_id = $currentUser->organization_id;

                if ($catalogBaseGoods->market_place == 1) {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status   = 1;
                        $catalogBaseGoods->save();
                        $message                       = Yii::t('app', 'Товар обновлен!');

                        return $this->renderAjax('catalogs/_success', ['message' => $message]);
                    }
                } else {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status   = 2;
                        $catalogBaseGoods->save();

                        $message = Yii::t('app', 'Товар обновлен!');
                        return $this->renderAjax('catalogs/_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('catalogs/_baseProductMarketPlaceForm', compact('catalogBaseGoods', 'countrys'));
    }

    public function actionMpCountryList($q)
    {
        if (Yii::$app->request->isAjax) {
            $model                      = new \common\models\MpCountry();
            Yii::$app->response->format = Response::FORMAT_JSON;
            //return 'aaa';
            return $model->ajaxsearch($q);
        }
        return false;
    }

    public function actionGetSubCat()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id   = end($_POST['depdrop_parents']);
            $list = \common\models\MpCategory::find()->select(['id', 'name'])->
                    andWhere(['parent' => $id])->
                    asArray()->
                    all();
            foreach ($list as &$item) {
                $item['name'] = Yii::t('app', $item['name']);
            }
            $selected = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                if (!empty($_POST['depdrop_params'])) {
                    $params = $_POST['depdrop_params'];
                    $id1    = $params[0]; // get the value of 1
                    $id2    = $params[1]; // get the value of 2
                    foreach ($list as $i => $cat) {
                        $out[] = ['id' => $cat['id'], 'name' => $cat['name']];
                        //if ($i == 0){$aux = $cat['id'];}
                        //($cat['id'] == $id1) ? $selected = $id1 : $selected = $aux;
                        //$selected = $id1;
                        if ($cat['id'] == $id1) {
                            $selected = $cat['id'];
                        }
                        if ($cat['id'] == $id2) {
                            $selected = $id2;
                        }
                    }
                }
                return Json::encode(['output' => $out, 'selected' => $selected]);
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
    }

    public function actionChangecatalogprop()
    {
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            // $CatalogBaseGoods = new CatalogBaseGoods;
            $id                         = \Yii::$app->request->post('id');
            $elem                       = \Yii::$app->request->post('elem');

            if ($elem == 'market') {
                $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);
                if ($CatalogBaseGoods->market_place == 0) {
                    $set = 1;
                } else {
                    $set = 0;
                }
                $CatalogBaseGoods->market_place = $set;
                $CatalogBaseGoods->update();

                $result = ['success' => true, 'status' => 'update market'];
                return $result;
            }
            if ($elem == 'status') {
                $CatalogBaseGoods = CatalogBaseGoods::findOne(['id' => $id]);
                if (empty($CatalogBaseGoods->status)) {
                    $set = CatalogBaseGoods::STATUS_ON;
                } else {
                    $set = CatalogBaseGoods::STATUS_OFF;
                }
                //CatalogBaseGoods::updateAll(['status' =>$set], ['id' => $id]);
                $CatalogBaseGoods->status = $set;
                $CatalogBaseGoods->update();

                $result = ['success' => true, 'status' => $set];
                return $result;
            }
        }
    }

    public function actionChangesetcatalog()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser                = User::findIdentity(Yii::$app->user->id);
            //$relation_supp_rest = new RelationSuppRest;
            $curCat                     = \Yii::$app->request->post('curCat'); //catalog
            $id                         = \Yii::$app->request->post('id'); //rest_org_id
            $state                      = Yii::$app->request->post('state');

            if ($state == 'true') {
                $rest_org_id                = $id;
                $relation_supp_rest         = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                $relation_supp_rest->cat_id = $curCat;
                $relation_supp_rest->status = 1;
                $relation_supp_rest->update();
                $rows                       = User::find()->where(['organization_id' => $rest_org_id])->all();
                foreach ($rows as $row) {
                    if ($row->profile->phone && $row->profile->sms_allow) {
                        $text   = Yii::t('app', 'Поставщик ') . $currentUser->organization->name . Yii::t('app', ' назначил для Вас каталог в системе');
                        $target = $row->profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
                }
                return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.subscr', ['ru' => 'Подписан'])]);
            } else {
                $rest_org_id                = $id;
                $relation_supp_rest         = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                $relation_supp_rest->status = 0;
                $relation_supp_rest->update();
                return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.subscr_not_two', ['ru' => 'Не подписан'])]);
            }
        }
    }

    public function actionChangecatalogstatus()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id                         = \Yii::$app->request->post('id');
            $catalog                    = Catalog::findOne(['id' => $id, 'type' => Catalog::CATALOG]);
            if (isset($catalog)) {
                $catalog->status = \Yii::$app->request->post('state') == 'true' ? 1 : 0;
                $catalog->update();
            }
            $result = ['success' => true, 'status' => 'update status'];
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

    public function actionAjaxDeleteUser()
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($post && isset($post['id'])) {
                $user = User::findOne(['id' => $post['id']]);

                $relations = RelationUserOrganization::findAll(['organization_id' => $this->currentUser->organization_id]);

                $usersCount = count($relations);
                if ($user->id == $this->currentUser->id && $usersCount < 2) {
                    $message = Yii::t('message', 'frontend.controllers.client.maybe', ['ru' => 'Может воздержимся от удаления себя?']);
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1)) {
                    if ($user->id == $this->currentUser->id) {
                        $rel2 = RelationUserOrganization::find()->where(['user_id' => $post['id']])->andWhere(['not', ['organization_id' => $this->currentUser->organization_id]])->all();
                        if (count($rel2) > 0) {
                            $transaction = \Yii::$app->db->beginTransaction();
                            try {
                                $user->organization_id = $rel2[0]->organization_id;
                                if (!(in_array($user->role_id, Role::getAdminRoles()) || in_array($user->role_id, Role::getFranchiseeEditorRoles()))) {
                                    $user->role_id = $rel2[0]->role_id;
                                }
                                //$profile->email = $user->getEmail();
                                $user->save();
                                User::deleteRelationUserOrganization($post['id'], $this->currentUser->organization_id);
                                Yii::$app->user->logout();

                                $transaction->commit();

                                return $this->goHome();
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
                            }
                        } else {
                            $message = Yii::t('message', 'frontend.controllers.client.maybe', ['ru' => 'Может воздержимся от удаления себя?']);
                            return $this->renderAjax('settings/_success', ['message' => $message]);
                        }
                    }

                    $isExists = User::deleteUserFromOrganization($post['id'], $this->currentUser->organization_id);
                    if ($isExists && $user->id != $this->currentUser->id) {
                        $message = Yii::t('message', 'frontend.controllers.client.user_deleted', ['ru' => 'Пользователь удален!']);
                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
//                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save()) {
                        $message = Yii::t('message', 'frontend.controllers.vendor.user_added', ['ru' => 'Пользователь удален!']);
                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
                }
            }
        }
        $message = Yii::t('app', 'Не удалось удалить пользователя!');
        return $this->renderAjax('settings/_success', ['message' => $message]);
    }

    public function actionStep1()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $catalog                    = new Catalog();
            $post                       = Yii::$app->request->post();
            $currentUser                = User::findIdentity(Yii::$app->user->id);
            if ($catalog->load($post)) {
                $catalog->supp_org_id = $currentUser->organization_id;
                $catalog->type        = Catalog::CATALOG;
                $catalog->status      = 1;
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_eleven', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.cat_error_fourteen', ['ru' => 'Укажите корректное  <strong>Имя</strong> каталога'])]];
                    return $result;
                }
            } else {
                return (['success' => false, 'type' => 2, 'POST не определен']);
            }
        }
        $catalog = new Catalog();
        return $this->render('newcatalog/step-1', compact('catalog'));
    }

    public function actionStep1Update($id)
    {
        $cat_id      = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (!Catalog::find()->where(['id' => $id, 'supp_org_id' => $currentUser->organization_id])->exists()) {
            return $this->redirect(['vendor/index']);
        }
        $catalog = Catalog::find()->where(['id' => $cat_id])->one();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post                       = Yii::$app->request->post();
            if ($catalog->load($post)) {
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_eleven', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.cat_error_fourteen', ['ru' => 'Укажите корректное  <strong>Имя</strong> каталога'])]];
                    return $result;
                }
            }
        }
        return $this->render('newcatalog/step-1', compact('catalog', 'cat_id', 'searchModel', 'dataProvider'));
    }

    public function actionStep1Clone($id)
    {
        $cat_id_old  = $id; //id исходного каталога
        $currentUser = User::findIdentity(Yii::$app->user->id);

        $model = Catalog::findOne(['id' => $id, 'supp_org_id' => $currentUser->organization_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $model->id          = null;
        $model->name        = $model->name . ' ' . date('H:i:s');
        $cat_type           = $model->type;   //текущий тип каталога(исходный)
        $model->type        = Catalog::CATALOG; //переопределяем тип на 2
        $model->status      = 1;
        $model->isNewRecord = true;
        $model->save();

        $cat_id = $model->id; //новый каталог id
        if ($cat_type == Catalog::BASE_CATALOG) {
            $sql = "insert into " . CatalogGoods::tableName() .
                "(cat_id,base_goods_id,price,created_at) "
                    . "SELECT " . $cat_id . ", id, price, NOW() from " . CatalogBaseGoods::tableName() . " WHERE cat_id = $cat_id_old and deleted<>1";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        if ($cat_type == Catalog::CATALOG) {
            $sql = "insert into " . CatalogGoods::tableName() .
                "(cat_id,base_goods_id,price,created_at) "
                    . "SELECT " . $cat_id . ", base_goods_id, price, NOW() from " . CatalogGoods::tableName() . " WHERE cat_id = $cat_id_old";
            \Yii::$app->db->createCommand($sql)->execute();
        }

        return $this->redirect(['vendor/step-1-update', 'id' => $cat_id]);
    }

    public function actionStep2AddProduct()
    {
        if (Yii::$app->request->isAjax) {
            $product_id                 = Yii::$app->request->post('baseProductId');
            $cat_id                     = Yii::$app->request->post('cat_id');
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('state') == 'true') {
                $product_id                  = Yii::$app->request->post('baseProductId');
                $catalogGoods                = new CatalogGoods;
                $catalogGoods->base_goods_id = $product_id;
                $catalogGoods->cat_id        = $cat_id;
                $catalogGoods->price         = CatalogBaseGoods::findOne(['id' => $product_id])->price;
                $catalogGoods->save();
                return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.added', ['ru' => 'Добавлен'])]);
            } else {
                CatalogGoods::deleteAll(['base_goods_id' => $product_id, 'cat_id' => $cat_id]);
                return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.deleted', ['ru' => 'Удален'])]);
            }
        }
    }

    public function actionStep2($id)
    {
        $sort = \Yii::$app->request->get('sort');

        $cat_id      = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('check')) {
                if (CatalogGoods::find()->where(['cat_id' => $cat_id])->exists()) {
                    return (['success' => true, 'cat_id' => $cat_id]);
                } else {
                    return (['success' => false, 'type' => 1, 'message' => Yii::t('error', 'frontend.controllers.vendor.empty_cat', ['ru' => 'Пустой каталог'])]);
                }
            }
        }

        $baseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($baseCatalog)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out_three', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $baseCurrencySymbol = $baseCatalog->currency->symbol;

        $tblCBG = CatalogBaseGoods::tableName();
        $q = CatalogBaseGoods::find()->where('deleted = 0');
        $q->andWhere('cat_id = ' . $baseCatalog->id);

        $q->select([
            '*',
            "case when LENGTH(article) != 0 then 1 ELSE 0 end as len",
            "(article + 0) AS c_article_1",
            "article AS c_article",
            new Expression("article REGEXP '^-?[0-9]+$' AS i"),
            new Expression("product REGEXP '^-?[а-яА-Я].*$' AS alf_cyr")
        ]);

        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = trim(\Yii::$app->request->get('searchString'));
            $q->andWhere('product LIKE :p OR article LIKE :a');
            $q->addParams([':a' => "%" . $searchString . "%", ':p' => "%" . $searchString . "%"]);
        }

        if ($sort == 'product') {
            $q->orderBy('alf_cyr DESC, product ASC');
        } elseif ($sort == '-product') {
            $q->orderBy('alf_cyr ASC, product DESC');
        }

        if ($sort == 'article') {
            $q->orderBy('len DESC, i DESC, (article + 0), article');
        } elseif ($sort == '-article') {
            $q->orderBy('len DESC, i ASC, (article + 0) DESC, article DESC');
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $q,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes'   => [
                    'product',
                    'price',
                    'article',
                    'units',
                    'status',
                    'category_id',
                    'ed',
                    'market_place',
                    'c_article_1',
                    'c_article',
                    'i',
                    'len'
                ],
                'defaultOrder' => [
                    'len'         => SORT_DESC,
                    'i'           => SORT_DESC,
                    'c_article_1' => SORT_ASC,
                    'c_article'   => SORT_ASC
                ]
            ],
        ]);

        return $this->render('newcatalog/step-2', compact('dataProvider', 'cat_id', 'baseCurrencySymbol'));
    }

    public function actionStep3Copy($id)
    {
        ini_set('memory_limit', '256M');
        $cat_id         = $id;
        $currentUser    = User::findIdentity(Yii::$app->user->id);
        $model          = Catalog::findOne(['id' => $id, 'supp_org_id' => $currentUser->organization_id]);
        $currentCatalog = $model;
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out_four', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $export = Yii::$app->request->post('export_type') ?? null;
        if (Yii::$app->request->isPost && !$export) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $arrCatalog                 = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            $numberPattern              = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            $catalogGoods               = CatalogGoods::find()
                            ->select(['catalog_goods.id', 'catalog_goods.price'])
                            ->joinWith('baseProduct', false)
                            ->where([
                                'catalog_base_goods.supp_org_id' => $currentUser->organization_id, 'catalog_goods.cat_id'           => $id])->asArray()->all();
            $catalogGoods               = \yii\helpers\ArrayHelper::map($catalogGoods, 'id', 'price');
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = (int) (trim($arrCatalogs['dataItem']['goods_id']));
                $price    = floatval(trim(str_replace(',', '.', $arrCatalogs['dataItem']['total_price'])));

                if (!isset($goods_id)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_thirteen', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('app', 'Неверный товар')]];
                    return $result;
                }

                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => Yii::t('error', 'frontend.controllers.vendor.oops_thirteen', ['ru' => 'УПС! Ошибка']), 'body' => Yii::t('error', 'frontend.controllers.vendor.wrong_price_two', ['ru' => 'Неверный формат <strong>Цены</strong><br><small>только число в формате 0,00</small>'])]];
                    return $result;
                }
            }

            $cgTable     = CatalogGoods::tableName();
            $data_update = '';
            $batch       = 0;
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = (int) (trim($arrCatalogs['dataItem']['goods_id']));
                $price    = floatval(str_replace(',', '.', trim($arrCatalogs['dataItem']['total_price'])));

                if ($price != $catalogGoods[$goods_id]) {
                    if ($batch < 1000) {
                        $data_update .= "UPDATE $cgTable set price = $price where cat_id=$id and id=$goods_id;";
                        $batch++;
                    } else {
                        Yii::$app->db->createCommand($data_update)->execute();
                        $data_update = "UPDATE $cgTable set price = $price where cat_id=$id and id=$goods_id;";
                        $batch       = 0;
                    }
                }
            }
            if (!empty($data_update)) {
                Yii::$app->db->createCommand($data_update)->execute();
            }
            $result = ['success' => true, 'alert' => ['class' => 'success-fk', 'title' => Yii::t('message', 'frontend.controllers.vendor.saved', ['ru' => 'Сохранено']), 'body' => Yii::t('message', 'frontend.controllers.vendor.upd_data', ['ru' => 'Данные успешно обновлены'])]];
            return $result;
        } else {
            $sql = "SELECT "
                    . "catalog.id as id,"
                    . "article,"
                    . "case when LENGTH(article) != 0 then 1 ELSE 0 end as len,"
                . "(article + 0) AS c_article_1,"
                . "article REGEXP '^-?[0-9]+$' AS i,"
                    . "catalog_base_goods.product as product,"
                    . "catalog_base_goods.id as base_goods_id,"
                    . "catalog_goods.id as goods_id,"
                    . "units,"
                    . "ed,"
                    . "catalog_base_goods.price as base_price,"
                    . "catalog_goods.price as price,"
                    . "catalog_base_goods.status"
                . " FROM catalog "
                    . "LEFT JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                    . "LEFT JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id "
                    . "WHERE catalog.id = $id and catalog_base_goods.deleted != 1 and catalog_base_goods.status = 1 "
                    . "and catalog_base_goods.supp_org_id = {$currentUser->organization_id} "
                    . "ORDER BY len DESC, i DESC, c_article_1 ASC, article ASC ";

            $arr = \Yii::$app->db->createCommand($sql)->queryAll();

            $array = [];
            foreach ($arr as $arrs) {
                $c_article       = $arrs['article'];
                $c_product       = Html::decode(Html::decode(Html::decode($arrs['product'])));
                $c_base_goods_id = $arrs['base_goods_id'];
                $c_goods_id      = $arrs['goods_id'];
                $c_base_price    = $arrs['base_price'];
                $c_ed            = $arrs['ed'];
                $c_price         = $arrs['price'];

                array_push($array, [
                    'article'       => $c_article,
                    'product'       => $c_product,
                    'base_goods_id' => $c_base_goods_id,
                    'goods_id'      => $c_goods_id,
                    'base_price'    => $c_base_price,
                    'price'         => $c_price,
                    'ed'            => $c_ed,
                    'total_price'   => $c_price]);
            }
        }

        $sort        = \Yii::$app->request->get('sort') ?? '';
        $baseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($baseCatalog)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        $dataProvider = CatalogBaseGoods::getDataForExcelExport($model, $sort);

        return $this->render('newcatalog/step-3-copy', compact('array', 'cat_id', 'currentCatalog', 'dataProvider'));
    }

    public function actionStep3UpdateProduct($id)
    {
        $catalogGoods = CatalogGoods::find()->where(['id' => $id])->one();
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogGoods->load($post)) {
                if ($catalogGoods->validate()) {

                    $catalogGoods->save();

                    $message = Yii::t('message', 'frontend.controllers.vendor.upd_good', ['ru' => 'Продукт обновлен!']);
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_productForm', compact('catalogGoods'));
    }

    public function actionStep4($id)
    {
        $cat_id      = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $model       = Catalog::findOne(['id' => $id, 'supp_org_id' => $currentUser->organization_id]);
        if (empty($model)) {
            throw new \yii\web\HttpException(404, Yii::t('error', 'frontend.controllers.vendor.get_out_six', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        $searchModel  = new RelationSuppRest;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('add-client')) {
                if (Yii::$app->request->post('state') == 'true') {
                    $rest_org_id                = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest         = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                    $relation_supp_rest->cat_id = $cat_id;
                    $relation_supp_rest->status = 1;
                    $relation_supp_rest->update();
                    $rows                       = User::find()->where(['organization_id' => $rest_org_id])->all();
                    foreach ($rows as $row) {
                        if ($row->profile->phone && $row->profile->sms_allow) {
                            $text   = Yii::t('app', 'Поставщик ') . $currentUser->organization->name . Yii::t('app', ' назначил для Вас каталог в системе');
                            $target = $row->profile->phone;
                            Yii::$app->sms->send($text, $target);
                        }
                    }
                    return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.subscr_two', ['ru' => 'Подписан'])]);
                } else {
                    $rest_org_id                = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest         = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                    $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                    $relation_supp_rest->status = 0;
                    $relation_supp_rest->update();
                    return (['success' => true, Yii::t('message', 'frontend.controllers.vendor.subscr_not', ['ru' => 'Не подписан'])]);
                }
            }
        }
        return $this->render('newcatalog/step-4', compact('searchModel', 'dataProvider', 'currentCatalog', 'cat_id'));
    }

    public function actionAjaxAddClient()
    {
        $user = new User(['scenario' => 'sendInviteFromVendor']);
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                if ($user->validate()) {
                    $user->setScenario('sendInviteFromVendor2');
                    if (!$user->validate()) {
                        $user = User::findOne(['email' => $user->email]);
                        $user->setScenario('sendInviteFromActiveVendor');
                        if ($user->validate()) {
                            $currentUser = User::findIdentity(Yii::$app->user->id);
                            $user->setScenario('sendInviteFromActiveVendor2');
                            if ($user->validate()) {
                                $relationSuppRestPotential = new RelationSuppRestPotential();

                                if (Catalog::find()->where(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
                                    $supp_base_cat_id                  = Catalog::find()->where(['supp_org_id' => $currentUser->organization_id, 'type' => 1])->one()->id;
                                    $relationSuppRestPotential->cat_id = $supp_base_cat_id;
                                }
                                $relationSuppRestPotential->rest_org_id  = $user->organization_id;
                                $relationSuppRestPotential->supp_org_id  = $currentUser->organization_id;
                                $relationSuppRestPotential->invite       = RelationSuppRest::INVITE_ON;
                                $relationSuppRestPotential->status       = 3;
                                $relationSuppRestPotential->supp_user_id = $currentUser->id;
                                $relationSuppRestPotential->save();
                            }
                            $this->currentUser->sendInviteToActiveClient($user);
                            $message = Yii::t('message', 'frontend.controllers.vendor.inv_sent', ['ru' => 'Приглашение отправлено!']);
                            return $this->renderAjax('clients/_success', ['message' => $message]);
                        }
                    } else {
                        $this->currentUser->sendInviteToClient($user);

                        $message = Yii::t('message', 'frontend.controllers.vendor.inv_sent', ['ru' => 'Приглашение отправлено!']);
                        return $this->renderAjax('clients/_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('clients/_addClientForm', compact('user'));
    }

    public
            function actionAjaxSetPercent($id)
    {
        $cat_id       = $id;
        $catalogGoods = new CatalogGoods(['scenario' => 'update']);
        if (Yii::$app->request->isAjax) {
            $post                 = Yii::$app->request->post();
            $catalogGoods->cat_id = $cat_id;
            if ($catalogGoods->load($post)) {
                if ($catalogGoods->validate()) {

                    $catalogGoods = CatalogGoods::updateAll(['price' => 'price' - (('price' / 100) * $catalogGoods->discount_percent)], ['cat_id' => $cat_id]);
                    $message      = Yii::t('message', 'frontend.controllers.vendor.saved_two', ['ru' => "Сохранено!"]);
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_setPercentCatalog', compact('catalogGoods', 'cat_id'));
    }

    public function actionViewClient($id)
    {
        $client_id          = $id;
        $currentUser        = User::findIdentity(Yii::$app->user->id);
        $canManage          = Yii::$app->user->can('manage');
        $vendor             = $currentUser->organization;
        $organization       = Organization::find()->where(['id' => $client_id])->one();
        $relation_supp_rest = RelationSuppRest::find()->where([
                    'rest_org_id' => $client_id,
                    'supp_org_id' => $currentUser->organization_id])->one();
        $curCatalog         = $relation_supp_rest->cat_id;
        $catalogs           = \yii\helpers\ArrayHelper::map(Catalog::find()->
                                where(['supp_org_id' => $currentUser->organization_id])->
                                all(), 'id', 'name');
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($relation_supp_rest->load($post)) {
                if ($relation_supp_rest->validate()) {
                    if ($relation_supp_rest->cat_id != $curCatalog && !empty($relation_supp_rest->cat_id)) {
                        foreach ($organization->users as $recipient) {
                            if ($recipient->profile->phone && $recipient->profile->sms_allow) {
                                $text   = Yii::t('app', 'Поставщик ') . $currentUser->organization->name . Yii::t('app', ' назначил для Вас каталог в системе');
                                $target = $recipient->profile->phone;
                                Yii::$app->sms->send($text, $target);
                            }
                        }
                    }

                    $postedAssociatedIds   = Yii::$app->request->post("associatedManagers") ? Yii::$app->request->post("associatedManagers") : [];
                    $currentAssociatedIds  = array_keys($organization->getAssociatedManagersList($vendor->id));
                    $newAssociatedIds      = array_diff($postedAssociatedIds, $currentAssociatedIds);
                    $obsoleteAssociatedIds = array_diff($currentAssociatedIds, $postedAssociatedIds);
                    $transaction           = Yii::$app->db->beginTransaction();
                    try {
                        if (Yii::$app->user->can('manage') || in_array($currentUser->role_id, \common\models\Role::getFranchiseeEditorRoles())) {
                            foreach ($newAssociatedIds as $newId) {
                                $new                  = new ManagerAssociate();
                                $new->manager_id      = $newId;
                                $new->organization_id = $client_id;
                                $new->save();
                            }
                            foreach ($obsoleteAssociatedIds as $obsoleteId) {
                                $obsolete = ManagerAssociate::findOne(['manager_id' => $obsoleteId, 'organization_id' => $client_id]);
                                if ($obsolete) {
                                    $obsolete->delete();
                                }
                            }
                        }
                        $relation_supp_rest->update();
                        $transaction->commit();
                        $message = Yii::t('app', 'Сохранено');
                    } catch (Exception $e) {
                        $transaction->rollBack();
                        $message = Yii::t('app', 'Ошибка!');
                    }
                    return $this->renderAjax('clients/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('clients/_viewClient', compact('organization', 'relation_supp_rest', 'catalogs', 'client_id', 'vendor', 'canManage', 'currentUser'));
    }

    public
            function actionViewCatalog($id)
    {
        $cat_id      = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $catalog     = Catalog::find()->where(['id' => $cat_id])->one();
        if (empty($catalog)) {
            return;
        }
        $currencySymbol = $catalog->currency->symbol;
        if ($catalog->type == Catalog::BASE_CATALOG) {
            $searchModel  = new CatalogBaseGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id, null);
            return $this->renderAjax('catalogs/_viewBaseCatalog', compact('searchModel', 'dataProvider', 'cat_id', 'currencySymbol'));
        }
        if ($catalog->type == Catalog::CATALOG) {
            $searchModel  = new CatalogGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);
            return $this->renderAjax('catalogs/_viewCatalog', compact('searchModel', 'dataProvider', 'cat_id', 'currencySymbol'));
        }
    }

    public
            function actionListCatalog()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $search      = Yii::$app->request->post('search');
        $restaurant  = Yii::$app->request->post('restaurant');

        return $this->renderAjax('catalogs/_listCatalog', compact('currentUser', 'search', 'restaurant'));
    }

    public
            function actionMessages()
    {
        return $this->render('/site/underConstruction');
    }

    public
            function actionEvents()
    {
        return $this->render('/site/underConstruction');
    }

    public
            function actionAnalytics()
    {

        $currentUser = $this->currentUser;
        $vendor      = $currentUser->organization;

        $orderTable = Order::tableName();
        $maTable    = ManagerAssociate::tableName();
        $cbgTable   = CatalogBaseGoods::tableName();
        $rspTable   = RelationSuppRest::tableName();

        //---header stats start
        $headerStats["goodsCount"] = CatalogBaseGoods::find()
                ->where(["supp_org_id" => $vendor->id, "status" => CatalogBaseGoods::STATUS_ON, "deleted" => CatalogBaseGoods::DELETED_OFF])
                ->count();

        if (Yii::$app->user->can('manage')) {
            $headerStats["ordersCount"]   = Order::find()
                    ->where(["vendor_id" => $vendor->id])->andWhere(['not in', 'status', [OrderStatus::STATUS_FORMING]])
                    ->count();
            $headerStats["clientsCount"]  = RelationSuppRest::find()
                    ->where(["supp_org_id" => $vendor->id])
                    ->count();
            $headerStats["totalTurnover"] = Order::find()
                    ->where(['vendor_id' => $vendor->id, 'status' => [OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT, OrderStatus::STATUS_PROCESSING, OrderStatus::STATUS_DONE]])
                    ->sum('total_price');
        } else {
            $headerStats["ordersCount"]   = Order::find()
                    ->leftJoin($maTable, "$maTable.organization_id = $orderTable.client_id")
                    ->where(["vendor_id" => $vendor->id, "$maTable.manager_id" => $currentUser->id])
                    ->count();
            $headerStats["clientsCount"]  = RelationSuppRest::find()
                    ->leftJoin($maTable, "$maTable.organization_id = $rspTable.rest_org_id")
                    ->where(["supp_org_id" => $vendor->id, "$maTable.manager_id" => $currentUser->id])
                    ->count();
            $headerStats["totalTurnover"] = Order::find()
                    ->leftJoin($maTable, "$maTable.organization_id = $orderTable.client_id")
                    ->where(['vendor_id' => $vendor->id, "$maTable.manager_id" => $currentUser->id, 'status' => [OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR, OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT, OrderStatus::STATUS_PROCESSING, OrderStatus::STATUS_DONE]])
                    ->sum('total_price');
        }
        //---header stats end
        $filter_get_employee = yii\helpers\ArrayHelper::map(\common\models\Profile::find()->
                                where(['in', 'user_id', \common\models\User::find()->
                                    select('id')->
                                    where(['organization_id' => $currentUser->organization_id])])->all(), 'user_id', 'full_name');

        $filter_restaurant = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                where(['in', 'id', \common\models\RelationSuppRest::find()->
                                    select('rest_org_id')->
                                    where(['supp_org_id' => $currentUser->organization_id, 'invite' => '1'])])->all(), 'id', 'name');
        $filter_status     = "";
        $filter_from_date  = date("d-m-Y", strtotime(" -2 months"));
        $filter_to_date    = date("d-m-Y");
        $filter_client     = "";
        $where             = "";

        //pieChart
        function hex()
        {
            $hex = '#';
            foreach (['r', 'g', 'b'] as $color) {
                //случайное число в диапазоне 0 и 255.
                $val    = mt_rand(0, 255);
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
            $filter_employee  = trim(\Yii::$app->request->get('filter_employee'));
            $filter_status    = trim(\Yii::$app->request->get('filter_status'));
            $filter_from_date = trim(\Yii::$app->request->get('filter_from_date'));
            $filter_to_date   = trim(\Yii::$app->request->get('filter_to_date'));
            $filter_client    = trim(\Yii::$app->request->get('filter_client'));

            empty($filter_status) ? "" : $where .= " and status='" . $filter_status . "'";
            empty($filter_client) ? "" : $where .= " and client_id='" . $filter_client . "'";
            empty($filter_employee) ? "" : $where .= " and accepted_by_id='" . $filter_employee . "'";
        }

        $currencyList = Currency::getAnalCurrencyList($currentUser->organization_id, $filter_from_date, $filter_to_date, 'vendor_id');

        $filter_currency = trim(\Yii::$app->request->get('filter_currency', key($currencyList)));
        empty($filter_currency) ? $where           .= " and currency_id='1'" : $where           .= " and currency_id='" . $filter_currency . "'";

        // Объем продаж чарт
        if (Yii::$app->user->can('manage')) {
            $area_chart = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
                (select sum(total_price) FROM $orderTable 
                where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at and 
                vendor_id = $currentUser->organization_id and status<>" . OrderStatus::STATUS_FORMING . " and ("
                            . "DATE(created_at) between '" .
                            date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                            date('Y-m-d', strtotime($filter_to_date)) . "')" .
                            $where .
                ") AS total_price
                FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS created_at 
                FROM $orderTable where 
                vendor_id = $currentUser->organization_id and status<>" . OrderStatus::STATUS_FORMING . " and("
                            . "DATE(created_at) between '" .
                            date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                date('Y-m-d', strtotime($filter_to_date)) . "')" . $where . ")tb")->queryAll();
        } else {
            $area_chart = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
                (select sum(total_price) FROM $orderTable LEFT JOIN $maTable ON $orderTable.client_id = $maTable.organization_id 
                where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at AND $maTable.manager_id = $currentUser->id AND
                vendor_id = $currentUser->organization_id and status<>" . OrderStatus::STATUS_FORMING . " and ("
                            . "DATE(created_at) between '" .
                            date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                            date('Y-m-d', strtotime($filter_to_date)) . "')" .
                            $where .
                ") AS total_price
                FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS created_at 
                FROM $orderTable LEFT JOIN $maTable ON $orderTable.client_id = $maTable.organization_id WHERE 
                vendor_id = $currentUser->organization_id AND $maTable.manager_id = $currentUser->id and status<>" . OrderStatus::STATUS_FORMING . " and("
                            . "DATE(created_at) between '" .
                            date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                date('Y-m-d', strtotime($filter_to_date)) . "')" . $where . ")tb")->queryAll();
        }
        $arr_create_at = [];
        $arr_price     = [];
        if (count($area_chart) == 1) {
            array_push($arr_create_at, 0);
            array_push($arr_price, 0);
        }
        foreach ($area_chart as $area_charts) {
            array_push($arr_create_at, Yii::$app->formatter->asDatetime($area_charts['created_at'], "php:j M Y"));
            array_push($arr_price, $area_charts['total_price']);
        }

        $query        = Yii::$app->db->createCommand("
            SELECT 
              sum(price*quantity) as price, 
              product_id,
              c.iso_code
            FROM order_content oc 
            LEFT JOIN " . Order::tableName() . " o ON o.id = oc.order_id
            LEFT JOIN currency c ON c.id = o.currency_id
            WHERE order_id in (
                SELECT id from " . Order::tableName() . " where 
                (DATE(created_at) between '" .
                date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "')" .
                "and status<>" . OrderStatus::STATUS_FORMING . " and vendor_id = " . $currentUser->organization_id .
                $where .
                ") group by product_id");
        $totalCount   = Yii::$app->db->createCommand("
            SELECT COUNT(*) from (
            SELECT sum(price*quantity) as price, product_id FROM order_content WHERE order_id in (
                SELECT id from " . Order::tableName() . " where 
                (DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "')" .
                        "and status<>" . OrderStatus::STATUS_FORMING . " and vendor_id = " . $currentUser->organization_id .
                        $where .
                        ") group by product_id)tb")->queryScalar();
        $total_price = Yii::$app->db->createCommand("SELECT sum(total_price) as total from " . Order::tableName() . " where " .
                        "vendor_id = " . $currentUser->organization_id .
                        " and status<>" . OrderStatus::STATUS_FORMING . " and DATE_FORMAT(created_at,'%Y-%m-%d') between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "'" . $where)->queryOne();
        $total_price  = $total_price['total'];
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql'        => $query->sql,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 7,
            ],
            'sort'       => [
                'attributes'   => [
                    'product_id',
                    'price'
                ],
                'defaultOrder' => [
                    'price' => SORT_DESC
                ]
            ],
        ]);

        $clients_query      = Yii::$app->db->createCommand("
            SELECT client_id,sum(total_price) as total_price FROM " . Order::tableName() . " WHERE  
                (DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "') " .
                        $where .
                        " and vendor_id = " . $currentUser->organization_id .
                        " and status<>" . OrderStatus::STATUS_FORMING . " group by client_id order by total_price DESC")->queryAll();
        $arr_clients_price  = [];
        $arr_clients_labels = [];
        $arr_clients_colors = [];
        foreach ($clients_query as $clients_querys) {
//            $arr = array(
//                'value' => $clients_querys['total_price'],
//                'label' => \common\models\Organization::find()->where(['id' => $clients_querys['client_id']])->one()->name,
//                'color' => hex()
//            );
//            array_push($arr_clients_price, $arr);
            $arr_clients_price[]  = $clients_querys['total_price'];
            $arr_clients_labels[] = \common\models\Organization::find()->where(['id' => $clients_querys['client_id']])->one()->name;
            $arr_clients_colors[] = hex();
        }
        //$arr_clients_price = json_encode($arr_clients_price);
        $organizationId = $currentUser->organization_id;

        return $this->render('analytics/index', compact('currencyList', 'filter_restaurant', 'headerStats', 'filter_status', 'filter_from_date', 'filter_to_date', 'filter_client', 'arr_create_at', 'arr_price', 'dataProvider', 'arr_clients_price', 'arr_clients_labels', 'arr_clients_colors', 'total_price', 'filter_get_employee', 'organizationId'
        ));
    }

    public function actionAjaxUpdateCurrency()
    {
        $filter_from_date = \Yii::$app->request->get('filter_from_date') ? trim(\Yii::$app->request->get('filter_from_date')) : date("d-m-Y", strtotime(" -2 months"));
        $filter_to_date   = \Yii::$app->request->get('filter_to_date') ? trim(\Yii::$app->request->get('filter_to_date')) : date("d-m-Y");
        $currencyId       = \Yii::$app->request->get('filter_currency') ?? 1;
        $organizationId   = (int) \Yii::$app->request->get('organization_id');
        $currencyList     = Currency::getAnalCurrencyList($organizationId, $filter_from_date, $filter_to_date, 'vendor_id');
        $count            = count($currencyList);

        return $this->renderPartial('analytics/currency', compact('currencyList', 'count', 'currencyId'));
    }

    /*
     *  index
     */

    public function actionIndex()
    {
        $currentUser      = User::findIdentity(Yii::$app->user->id);
        //ГРАФИК ПРОДАЖ ----->
        $filter_from_date = date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date   = date("d-m-Y");

        $managerCondition = Yii::$app->user->can('manage') ? '' : "AND manager_associate.manager_id = $currentUser->id";
        $managerJoin = Yii::$app->user->can('manage') ? '' : "LEFT JOIN manager_associate ON order.client_id = manager_associate.organization_id ";

        $currencyList    = Currency::getAnalCurrencyList($currentUser->organization_id, $filter_from_date, $filter_to_date, 'vendor_id');
        $filter_currency = trim(\Yii::$app->request->get('filter_currency', key($currencyList)));
        empty($filter_currency) ? $where           = " and currency_id='1'" : $where           = " and currency_id='" . $filter_currency . "'";

        uksort($currencyList, function ($a, $b) use ($filter_currency) {
            return $a == $filter_currency ? -1 : 1;
        });

        $area_chart    = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
            (select sum(total_price) FROM " . Order::tableName() . " $managerJoin
            where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at and 
            vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . " and ("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "')" . $where .
            ") AS total_price
            FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS created_at 
            FROM " . Order::tableName() . " $managerJoin where 
            vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
            date('Y-m-d', strtotime($filter_to_date)) . "'))tb")->queryAll();
        $arr_create_at = [];
        $arr_price     = [];
        if (count($area_chart) == 1) {
            array_push($arr_create_at, 0);
            array_push($arr_price, 0);
        }
        foreach ($area_chart as $area_charts) {

            array_push($arr_create_at, Yii::$app->formatter->asDatetime($area_charts['created_at'], "php:j M Y"));
            array_push($arr_price, $area_charts['total_price']);
        }
        // <------ГРАФИК ПРОДАЖ
        //------>Статистика
        $stats                         = Yii::$app->db->createCommand("SELECT
            (SELECT sum(total_price) FROM " . Order::tableName() . " $managerJoin 
            WHERE vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and DATE_FORMAT(created_at, '%Y-%m-%d') = CURDATE()) as 'curDay',
            (SELECT sum(total_price) FROM " . Order::tableName() . " $managerJoin 
             WHERE vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and (MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())))
            as 'curMonth',
            (SELECT sum(total_price) FROM " . Order::tableName() . " $managerJoin 
            WHERE vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and YEAR(created_at) = YEAR(NOW()) AND WEEK(created_at, 1) = WEEK(NOW(), 1))
             as 'curWeek',
            (SELECT sum(total_price) FROM " . Order::tableName() . " $managerJoin 
            WHERE vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and MONTH(created_at) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(created_at) = YEAR(NOW()))
            as 'lastMonth',
            (SELECT sum(total_price) FROM " . Order::tableName() . " $managerJoin 
            WHERE vendor_id = $currentUser->organization_id $managerCondition and status<>" . OrderStatus::STATUS_FORMING . $where . " and MONTH(created_at) = MONTH(DATE_ADD(NOW(), INTERVAL -2 MONTH)) AND YEAR(created_at) = YEAR(NOW()))
            as 'TwoLastMonth'")->queryOne();
        // <-------Статистика
        //GRIDVIEW ИСТОРИЯ ЗАКАЗОВ ----->
        $searchModel                   = new \common\models\search\OrderSearch();
        $today                         = new \DateTime();
        $searchModel->date_from        = date("d.m.Y", strtotime(" -1 months"));
        $searchModel->vendor_id        = $currentUser->organization_id;
        $searchModel->vendor_search_id = $currentUser->organization_id;
        if (!Yii::$app->user->can('manage')) {
            $searchModel->manager_id = $currentUser->id;
        }

        $dataProvider             = $searchModel->search(null);
        $dataProvider->pagination = ['pageSize' => 10];
        // <----- GRIDVIEW ИСТОРИЯ ЗАКАЗОВ

        $organization = $currentUser->organization;
        $profile      = $currentUser->profile;

        return $this->render('index', compact(
                                'dataProvider', 'filter_from_date', 'filter_to_date', 'arr_create_at', 'arr_price', 'stats', 'organization', 'profile', 'currencyList'
        ));
    }

    public
            function actionTutorial()
    {
        return $this->render('tutorial');
    }

    public
            function actionSupport()
    {
        return $this->render('/site/underConstruction');
    }

    public
            function actionSidebar()
    {
        Yii::$app->session->get('sidebar-collapse') ?
                        Yii::$app->session->set('sidebar-collapse', false) :
                        Yii::$app->session->set('sidebar-collapse', true);
    }

    /**
     * changes currency in given catalog
     */
    public
            function actionAjaxChangeCurrency($id)
    {
        $newCurrencyId = Yii::$app->request->post('newCurrencyId');
        $catalog       = Catalog::find()->where(['id' => $id, 'supp_org_id' => $this->currentUser->organization_id])->one();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($catalog)) {
            return ['result' => 'error', 'message' => Yii::t('error', 'frontend.controllers.vendor.empty_cat_two', ['ru' => 'Каталог не найден!'])];
        }

        $currency = Currency::findOne(['id' => $newCurrencyId]);
        if (empty($currency)) {
            return ['result' => 'error', 'message' => Yii::t('error', 'frontend.controllers.vendor.empty_cat_two', ['ru' => 'Каталог не найден!'])];
        }

        $catalog->currency_id = $newCurrencyId;
        $catalog->save();
        return ['result' => 'success', 'symbol' => $currency->symbol, 'iso_code' => ' (' . $currency->iso_code . ')'];
    }

    /**
     * calculate prices with new currency
     */
    public
            function actionAjaxCalculatePrices($id)
    {
        $catalog = Catalog::find()->where(['id' => $id, 'supp_org_id' => $this->currentUser->organization_id])->one();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($catalog)) {
            return ['result' => 'error', 'message' => Yii::t('error', 'frontend.controllers.vendor.cat_not_found', ['ru' => 'Каталог не найден!'])];
        }

        $oldCurrencyUnits = floatval(str_replace(',', '.', Yii::$app->request->post('oldCurrencyUnits')));
        $newCurrencyUnits = floatval(str_replace(',', '.', Yii::$app->request->post('newCurrencyUnits')));
        if (($oldCurrencyUnits <= 0) || ($newCurrencyUnits <= 0)) {
            return ['result' => 'error', 'message' => Yii::t('error', 'frontend.controllers.vendor.wrong_curr', ['ru' => 'Некорректный курс!'])];
        }

        $attributes = ['price' => new \yii\db\Expression('price * ' . $newCurrencyUnits / $oldCurrencyUnits)];
        $condition  = ['cat_id' => $id];

        switch ($catalog->type) {
            case Catalog::BASE_CATALOG:
                $updated = CatalogBaseGoods::updateAll($attributes, $condition);
                break;
            case Catalog::CATALOG:
                $updated = CatalogGoods::updateAll($attributes, $condition);
                break;
        }

        $sql = "SELECT "
                . "catalog.id as id,"
                . "article,"
                . "case when LENGTH(article) != 0 then 1 ELSE 0 end as len,"
            . "(article + 0) AS c_article_1,"
            . "article REGEXP '^-?[0-9]+$' AS i,"
                . "catalog_base_goods.product as product,"
                . "catalog_base_goods.id as base_goods_id,"
                . "catalog_goods.id as goods_id,"
                . "units,"
                . "ed,"
                . "catalog_base_goods.price as base_price,"
                . "catalog_goods.price as price,"
                . "catalog_base_goods.status"
            . " FROM catalog "
                . "LEFT JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                . "LEFT JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id "
                . "WHERE catalog.id = $id and catalog_base_goods.deleted != 1 "
                . "ORDER BY len DESC, i DESC, c_article_1 ASC, article ASC ";

        $arr = \Yii::$app->db->createCommand($sql)->queryAll();

        $array = [];
        foreach ($arr as $arrs) {
            $c_article       = $arrs['article'];
            $c_product       = Html::decode(Html::decode(Html::decode($arrs['product'])));
            $c_base_goods_id = $arrs['base_goods_id'];
            $c_goods_id      = $arrs['goods_id'];
            $c_base_price    = $arrs['base_price'];
            $c_ed            = $arrs['ed'];
            $c_price         = $arrs['price'];

            array_push($array, [
                'article'       => $c_article,
                'product'       => $c_product,
                'base_goods_id' => $c_base_goods_id,
                'goods_id'      => $c_goods_id,
                'base_price'    => $c_base_price,
                'price'         => $c_price,
                'ed'            => $c_ed,
                'total_price'   => $c_price]);
        }

        return ['result' => 'success', 'data' => $array];
    }

    public
            function actionPayments()
    {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchModel = new PaymentSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('date desc');
        $dataProvider->query->andFilterWhere(['organization_id' => $currentUser->organization->id]);

        return $this->render('payments', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    public function actionChkmail()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result                     = User::checkInvitingUser(\Yii::$app->request->post('email'));
            return $result;
        }
    }

    public function actionAjaxChangeMainIndex()
    {
        $post = Yii::$app->request->post();
        if (isset($post['cat_id']) && isset($post['main_index'])) {
            $cat_id     = $post['cat_id'];
            $main_index = $post['main_index'];
        } else {
            return false;
        }
        $currentUser = $this->currentUser;
        $catalog     = Catalog::findOne(['id' => $cat_id, 'type' => Catalog::BASE_CATALOG]);
        if (!Catalog::isMainIndexValid($main_index) || empty($catalog)) {
            return false;
        }
        if ($catalog->positionsCount > 0) {
            return false;
        }
        $catalog->main_index = $main_index;
        return $catalog->save();
    }

    public function actionAjaxDeleteMainCatalog()
    {
        $currentUser = $this->currentUser;
        $catalog     = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (!empty($catalog)) {
            return $catalog->deleteAllProducts();
        }
        return false;
    }

    public function actionAjaxRestoreMainCatalogLatestSnapshot()
    {
        $currentUser = $this->currentUser;
        $catalog     = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (!empty($catalog)) {
            return $catalog->restoreLastSnapshot();
        }
        return false;
    }

}
