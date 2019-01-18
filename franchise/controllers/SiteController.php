<?php

namespace franchise\controllers;

use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\FranchiseeAssociate;
use common\models\RelationManagerLeader;
use common\models\RelationSuppRest;
use common\models\Request;
use common\models\RequestCallback;
use common\models\RequestCounters;
use common\models\RequestSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use common\models\forms\ServiceDesk;
use common\components\AccessRule;
use common\models\Role;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
use common\models\Order;
use common\models\CatalogBaseGoods;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\SpreadsheetService;

define('CLIENT_APP_NAME', 'sonorous-dragon-167308');
define('SERVICE_ACCOUNT_CLIENT_ID', '114798227950751078238');
define('SERVICE_ACCOUNT_EMAIL', 'f-keeper@sonorous-dragon-167308.iam.gserviceaccount.com');
define('SERVICE_ACCOUNT_PKCS12_FILE_PATH', Yii::getAlias('@common') . '/google/GoogleApiDocs-356b554846a5.p12');
define('CLIENT_KEY_PW', 'notasecret');

/**
 * Description of AppController
 *
 * @author sharaf
 */
class SiteController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

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
                'only'       => ['index', 'setting', 'service-desk', 'settings', 'promotion', 'users', 'create-user', 'update-user', 'delete-user', 'validate-user', 'catalog', 'get-sub', 'import-from-xls', 'ajax-delete-product', 'ajax-edit-catalog-form', 'requests', 'orders'],
                'rules'      => [
                    [
                        'actions' => ['index', 'setting', 'setting', 'service-desk', 'settings', 'promotion', 'users', 'create-user', 'update-user', 'delete-user', 'validate-user', 'catalog', 'get-sub', 'import-from-xls', 'ajax-delete-product', 'ajax-edit-catalog-form', 'requests', 'orders'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_FRANCHISEE_MANAGER,
                            Role::ROLE_FRANCHISEE_LEADER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
//             'denyCallback' => function($rule, $action) {
//              throw new \yii\web\HttpException(404 ,Yii::t('app', 'Нет здесь ничего такого, проходите, гражданин'));
//              } 
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays desktop.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $currencyList = Currency::getFullCurrencyList($this->currentFranchisee->id);
        $iso_code = Currency::getMostPopularIsoCode($this->currentFranchisee->id) ?? "RUB";
        $currencyId = key($currencyList);

        if (Yii::$app->request->get() && Yii::$app->request->isPjax) {
            $currencyId = Yii::$app->request->get('filter_currency');
            $currency = Currency::findOne($currencyId);
            $iso_code = $currency->iso_code;
        }
        //---graph start
        $query = "SELECT truncate(sum(total_price),1) as spent, year(o.created_at) as year, month(o.created_at) as month, day(o.created_at) as day "
            . "FROM " . Order::tableName() . " o LEFT JOIN franchisee_associate ON o.vendor_id = franchisee_associate.organization_id "
            . "where status in (" . Order::STATUS_PROCESSING . "," . Order::STATUS_DONE . "," . Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT . "," . Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR . ") "
            . "and o.created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY AND franchisee_associate.franchisee_id = " . $this->currentFranchisee->id . " ";
        if ($currencyId) {
            $query .= " AND currency_id=" . $currencyId . " ";
        }
        $query .= "group by year(o.created_at), month(o.created_at), day(o.created_at)";
        $command = Yii::$app->db->createCommand($query);
        $ordersByDay = $command->queryAll();
        $dayLabels = [];
        $dayTurnover = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . Yii::$app->formatter->asDatetime(strtotime("2000-$order[month]-01"), "php:M") . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
            $total += $order["spent"];
        }
        //---graph end

        $clientsCount = $client = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
            ->count();
        $vendorsCount = $client = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
            ->count();
        $totalCount = $clientsCount + $vendorsCount;

        $last30days = date("Y-m-d", strtotime(" -1 months"));

        $total30Count = $client = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id])
            ->andWhere([">", "organization.updated_at", $last30days])
            ->count();

        $vendorsStats30 = $this->currentFranchisee->getMyVendorsStats($last30days);
        $vendorsStats = $this->currentFranchisee->getMyVendorsStats(null, null, $currencyId);

        $params = Yii::$app->request->getQueryParams();
        $searchModel = new \franchise\models\OrderSearch();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, true, $currencyId);
        $totalIncome = 0;
        foreach ($dataProvider->getModels('Order') as $one) {
            $totalIncome += $one->total_price;
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, true, null);

        $franchiseeType = $this->currentFranchisee->type;
        return $this->render('index', compact('dataProvider', 'dayLabels', 'dayTurnover', 'total30Count', 'totalCount', 'clientsCount', 'vendorsCount', 'vendorsStats30', 'vendorsStats', 'franchiseeType', 'totalIncome', 'currencyList', 'iso_code', 'currencyId'));
    }

    public function actionSettings()
    {
        $franchisee = $this->currentFranchisee;
        if ($franchisee->load(Yii::$app->request->post())) {
            if ($franchisee->validate()) {
                $franchisee->save();
            }
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('settings', compact('franchisee'));
        } else {
            return $this->render('settings', compact('franchisee'));
        }
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionOrders()
    {
        $searchModel = new \franchise\models\OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = "01.02.2017";
        if (\Yii::$app->request->get('searchString')) {
            $searchModel['searchString'] = trim(\Yii::$app->request->get('searchString'));
        }
        if (\Yii::$app->request->get('status')) {
            $searchModel['status'] = trim(\Yii::$app->request->get('status'));
        }
        if (\Yii::$app->request->get('date_from')) {
            $searchModel['date_from'] = $searchModel->date_from = trim(\Yii::$app->request->get('date_from'));
        }
        if (\Yii::$app->request->get('date_to')) {
            $searchModel['date_to'] = $searchModel->date_to = trim(\Yii::$app->request->get('date_to'));
        }
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);
        $exportFilename = 'orders_' . date("Y-m-d_H-m-s");
        $exportColumns = (new Order())->getOrdersExportColumns();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact('searchModel', 'dataProvider', 'exportFilename', 'exportColumns'));
        } else {
            return $this->render('orders', compact('searchModel', 'dataProvider', 'exportFilename', 'exportColumns'));
        }
    }

    public function actionServiceDesk()
    {
        $model = new ServiceDesk();
        $franchise = $this->currentFranchisee;
        $franchise_user = $this->currentUser->profile;
        if ($model->load(Yii::$app->request->post())) {

            $model->region = '';
            $model->phone = str_replace("+", "", $franchise_user->phone);
            $model->fio = $franchise_user->full_name;

            if ($model->validate()) {
                $objClientAuth = new \Google_Client();
                $objClientAuth->setApplicationName(CLIENT_APP_NAME);
                $objClientAuth->setClientId(SERVICE_ACCOUNT_CLIENT_ID);

                $objClientAuth->setAssertionCredentials(new \Google_Auth_AssertionCredentials (
                    SERVICE_ACCOUNT_EMAIL,
                    ['https://spreadsheets.google.com/feeds', 'https://docs.google.com/feeds'],
                    file_get_contents(SERVICE_ACCOUNT_PKCS12_FILE_PATH),
                    CLIENT_KEY_PW
                ));

                $objClientAuth->getAuth()->refreshTokenWithAssertion();
                $objToken = json_decode($objClientAuth->getAccessToken());

                $accessToken = $objToken->access_token;

                /**
                 * Initialize the service request factory
                 */

                $serviceRequest = new DefaultServiceRequest($accessToken);
                ServiceRequestFactory::setInstance($serviceRequest);

                /**
                 * Get spreadsheet by title
                 */

                $spreadsheetTitle = 'f-keeper';
                $spreadsheetService = new SpreadsheetService();
                $spreadsheetFeed = $spreadsheetService->getSpreadsheetFeed();
                $spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);

                /**
                 * Get particular worksheet of the selected spreadsheet
                 */

                $worksheetTitle = 'Franchise';
                $worksheetFeed = $spreadsheet->getWorksheetFeed();
                $worksheet = $worksheetFeed->getByTitle($worksheetTitle);
                $listFeed = $worksheet->getListFeed();

                $listFeed->insert([
                    'fid'           => $franchise->id,
                    'fuid'          => $franchise_user->user_id,
                    'region'        => $model->region,
                    'fio'           => $model->fio,
                    'phone'         => $model->phone,
                    'priority'      => $model->priority,
                    'message'       => $model->body,
                    'startdatetime' => date("Y-m-d H:i:s")
                ]);
            }
        }
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('service-desk', [
                'model' => $model,
            ]);
        } else {
            return $this->render('service-desk', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Displays general settings
     *
     * @return mixed
     */

    /**
     * Displays franchise users list
     *
     * @return mixed
     */
    public function actionUsers()
    {
        /** @var \common\models\search\UserSearch $searchModel */
        $searchModel = new \franchise\models\UserSearch();
        //$params = Yii::$app->request->getQueryParams();
        $params['UserSearch'] = Yii::$app->request->post("UserSearch");
        $this->loadCurrentUser();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('employees', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('employees', compact('searchModel', 'dataProvider'));
        }
    }

    /*
     *  User validate
     */

    public function actionAjaxValidateUser()
    {
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

    public function actionAjaxCreateUser()
    {
        $user = new User(['scenario' => 'manageNew']);
        $profile = new Profile();
        $organizationType = Organization::TYPE_FRANCHISEE;
        $rel = new RelationManagerLeader();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);
                $rel->load($post);

                if ($user->validate() && $profile->validate()) {
                    $user->setRegisterAttributes($user->role_id, $post['User']['status'])->save();
                    $profile->setUser($user->id)->save();
                    if ($user->role_id == Role::ROLE_FRANCHISEE_MANAGER) {
                        $rel->manager_id = $user->id;
                        $rel->save();
                    }
                    $user->setFranchisee($this->currentFranchisee->id);
//                    $this->currentUser->sendEmployeeConfirmation($user);
                    // send email
                    $model = new Organization();
                    $model->sendGenerationPasswordEmail($user, true);
                    $message = Yii::t('app', 'franchise.controllers.user_added', ['ru' => 'Пользователь добавлен!']);
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }
        $leadersArray = $this->currentFranchisee->getFranchiseeEmployees();

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType', 'rel', 'leadersArray'));
    }

    /*
     *  User update
     */

    public function actionAjaxUpdateUser($id)
    {
        $user = User::find()
            ->joinWith("franchiseeUser")
            ->where([
                'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                'user.id'                       => $id
            ])
            ->one();
        $user->setScenario("manage");
        $profile = $user->profile;
        $organizationType = Organization::TYPE_FRANCHISEE;
        $rel = RelationManagerLeader::findOne(['manager_id' => $id]);
        if (!$rel) {
            $rel = new RelationManagerLeader();
        }
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post) && ($user->role_id !== Role::ROLE_FRANCHISEE_AGENT)) {
                $user->setRegisterAttributes($post['User']['role_id'], $post['User']['status'])->save();
                $profile->load($post);
                $rel->load($post);
                if ($user->validate() && $profile->validate()) {
                    $user->save();
                    $profile->save();
                    if (empty($post['RelationManagerLeader']['leader_id'])) {
                        $rel->delete();
                    } else {
                        $rel->save();
                    }
                    $message = Yii::t('app', 'franchise.controllers.user_updated', ['ru' => 'Пользователь обновлен!']);
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
            }
        }
        $leadersArray = $this->currentFranchisee->getFranchiseeEmployees();

        return $this->renderAjax('settings/_userForm', compact('user', 'profile', 'organizationType', 'rel', 'leadersArray'));
    }

    /*
     *  User delete (not actual delete, just remove organization relation)
     */

    public function actionAjaxDeleteUser()
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($post && isset($post['id'])) {
                $user = $user = User::find()
                    ->joinWith("franchiseeUser")
                    ->where([
                        'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                        'user.id'                       => $post["id"],
                    ])
                    ->one();
                $usersCount = count($this->currentFranchisee->franchiseeUsers);
                if ($user->id == $this->currentUser->id) {
                    $message = Yii::t('app', 'franchise.controllers.remove_yourself', ['ru' => 'Может воздержимся от удаления себя?']);
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1) && ($user->role_id !== Role::ROLE_FRANCHISEE_AGENT)) {
                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save() && $user->franchiseeUser->delete()) {
                        $message = Yii::t('app', 'franchise.controllers.removed', ['ru' => 'Пользователь удален!']);
                        return $this->renderAjax('settings/_success', ['message' => $message]);
                    }
                }
            }
        }
        $message = Yii::t('app', 'franchise.controllers.cant_remove', ['ru' => 'Не удалось удалить пользователя!']);
        return $this->renderAjax('settings/_success', ['message' => $message]);
    }

    /**
     * Displays promotion
     *
     * @return mixed
     */
    public function actionPromotion()
    {
        return $this->render('promotion');
    }

    public function actionAjaxEditCatalogForm($catalog = null, $catId = null)
    {
        $suppCatalog = $catalog;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (!$catalog) {
            $catalog = isset(Yii::$app->request->get()['catalog']) ?
                Yii::$app->request->get()['catalog'] :
                Yii::$app->request->post()['catalog'];
        }
        $product_id = isset(Yii::$app->request->get()['product_id']) ?
            Yii::$app->request->get()['product_id'] :
            $product_id = null;

        if (!empty(isset($product_id))) {
            $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $product_id])->one();
            $catalogBaseGoods->scenario = 'marketPlace';
            if (!empty($catalogBaseGoods->category_id)) {
                $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
                $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
            }
        } else {
            if ($catId) $catalog = $catId;
            $catalogBaseGoods = new CatalogBaseGoods(['scenario' => 'marketPlace']);
            $cat = \common\models\Catalog::findOne(['id' => $catalog]);
            $catalogBaseGoods->supp_org_id = $cat->supp_org_id;
        }

        $sql = "SELECT id, name FROM mp_country WHERE name = \"Россия\"
	UNION SELECT id, name FROM mp_country WHERE name <> \"Россия\"";
        $countrys = \Yii::$app->db->createCommand($sql)->queryAll();

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                if ($catId) {
                    $cat = Catalog::findOne($suppCatalog);
                    $catalogBaseGoods->cat_id = $cat->supp_org_id;
                    $catalogBaseGoods->supp_org_id = $cat->supp_org_id;
                }
                $catalogBaseGoods->status = CatalogBaseGoods::STATUS_ON;
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                if ($post && $catalogBaseGoods->validate()) {
                    $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                    $catalogBaseGoods->save();

                    $message = Yii::t('app', 'franchise.controllers.product_updated', ['ru' => 'Продукт обновлен!']);
                    return $this->renderAjax('catalog/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax(isset($catId) ? '/goods/_form' : 'catalog/_ajaxEditCatalogForm', compact('catalogBaseGoods', 'countrys', 'catalog'));
    }

    public function actionGetSubCat()
    {
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $list = \common\models\MpCategory::find()->select(['id', 'name'])->
            andWhere(['parent' => $id])->
            asArray()->
            all();
            $selected = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                if (!empty($_POST['depdrop_params'])) {
                    $params = $_POST['depdrop_params'];
                    $id1 = $params[0]; // get the value of 1
                    $id2 = $params[1]; // get the value of 2
                    foreach ($list as $i => $cat) {
                        $out[] = ['id' => $cat['id'], 'name' => $cat['name']];
                        if ($cat['id'] == $id1) {
                            $selected = $cat['id'];
                        }
                        if ($cat['id'] == $id2) {
                            $selected = $id2;
                        }
                    }
                }
                echo Json::encode(['output' => $out, 'selected' => $selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected' => '']);
    }

    public function actionAjaxDeleteProduct()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll([
                'deleted'   => CatalogBaseGoods::DELETED_ON,
                'es_status' => CatalogBaseGoods::ES_DELETED
            ], ['id' => $product_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }

    public function actionImportFromXls($id, $vendor_id = null)
    {
        set_time_limit(180);
        $cat = \common\models\Catalog::find()->where([
            'id'   => $id,
            'type' => \common\models\Catalog::BASE_CATALOG
        ])->one();

        $vendor = $cat->vendor;
        $importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            $unique = 'article'; //уникальное поле
            $sql_array_products = CatalogBaseGoods::find()->select($unique)->where(['cat_id' => $id, 'deleted' => 0])->asArray()->all();
            $count_array = count($sql_array_products);
            $arr = [];
            //массив артикулов из базы
            for ($i = 0; $i < $count_array; $i++) {
                array_push($arr, (string)$sql_array_products[$i][$unique]);
            }

            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'franchise.controllers.download_error', ['ru' => 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'])
                    . Yii::t('app', 'franchise.controllers.repeat_error', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $localFile = \PHPExcel_IOFactory::identify($path);
            $objReader = \PHPExcel_IOFactory::createReader($localFile);
            $objPHPExcel = $objReader->load($path);

            $worksheet = $objPHPExcel->getSheet(0);
            $highestRow = $worksheet->getHighestRow(); // получаем количество строк
            $highestColumn = $worksheet->getHighestColumn(); // а так можно получить количество колонок
            $newRows = 0;
            $xlsArray = [];
            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
                if (!in_array($row_article, $arr)) {
                    $newRows++;
                    array_push($xlsArray, (string)$row_article);
                }
            }
            if ($newRows > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'franchise.controllers.upload_error', ['ru' => 'Ошибка загрузки каталога<br>'])
                    . Yii::t('app', 'franchise.controllers.trying_to_upload', ['ru' => '<small>Вы пытаетесь загрузить каталог объемом больше ']) . CatalogBaseGoods::MAX_INSERT_FROM_XLS . Yii::t('app', 'franchise.controllers.positions', ['ru' => ' позиций (Новых позиций), обратитесь к нам и мы вам поможем'])
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            if (max(array_count_values($xlsArray)) > 1) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'franchise.controllers.upload_error_two', ['ru' => 'Ошибка загрузки каталога<br>'])
                    . Yii::t('app', 'franchise.controllers.same_art', ['ru' => '<small>Вы пытаетесь загрузить один или более позиций с одинаковым артикулом! Проверьте файл на наличие одинаковых артикулов! '])
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $data_insert = [];
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_article = (string)$worksheet->getCellByColumnAndRow(0, $row); //артикул
                    $row_product = $worksheet->getCellByColumnAndRow(1, $row); //наименование
                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                    $row_ed = $worksheet->getCellByColumnAndRow(4, $row); //единица измерения
                    $row_note = $worksheet->getCellByColumnAndRow(5, $row);  //Комментарий
                    if (!empty($row_article && $row_product && $row_price && $row_ed)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        if (!in_array($row_article, $arr)) {
                            $new_item = new CatalogBaseGoods;
                            $new_item->cat_id = $id;
                            $new_item->supp_org_id = $vendor->id;
                            $new_item->article = $row_article;
                            $new_item->product = $row_product;
                            $new_item->units = $row_units;
                            $new_item->price = $row_price;
                            $new_item->ed = $row_ed;
                            $new_item->note = $row_note;
                            $new_item->status = CatalogBaseGoods::STATUS_ON;
                            $new_item->save();
                            /*$data_insert[] = [
                                $id,
                                $vendor->id,
                                $row_article,
                                $row_product,
                                $row_units,
                                $row_price,
                                $row_ed,
                                $row_note,
                                CatalogBaseGoods::STATUS_ON
                            ];*/
                        } else {
                            $CatalogBaseGoods = CatalogBaseGoods::find()->where([
                                'cat_id'  => $id,
                                'article' => $row_article,
                                'deleted' => CatalogBaseGoods::DELETED_OFF
                            ])->one();
                            $CatalogBaseGoods->product = $row_product;
                            $CatalogBaseGoods->units = $row_units;
                            $CatalogBaseGoods->price = $row_price;
                            $CatalogBaseGoods->ed = $row_ed;
                            $CatalogBaseGoods->note = $row_note;
                            $CatalogBaseGoods->es_status = CatalogBaseGoods::ES_UPDATE;
                            $CatalogBaseGoods->save();
                        }
                    }
                }
                /*if (!empty($data_insert)) {
                    $db = Yii::$app->db;
                    $sql = $db->queryBuilder->batchInsert(CatalogBaseGoods::tableName(), [
                        'cat_id', 'supp_org_id', 'article', 'product', 'units', 'price', 'ed', 'note', 'status'
                    ], $data_insert);
                    Yii::$app->db->createCommand($sql)->execute();
                }*/
                $transaction->commit();
                unlink($path);
                return $this->redirect(['catalog/basecatalog', 'vendor_id' => $vendor_id, 'id' => $id]);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('success', Yii::t('app', 'franchise.controllers.saving_error', ['ru' => 'Ошибка сохранения, повторите действие'])
                    . Yii::t('app', 'franchise.controllers.error_repeat', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            }
        }
        return $this->renderAjax('catalog/_importCatalog', compact('importModel', 'vendor_id'));
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionRequests()
    {
        $searchModel = new RequestSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = "01.02.2017";
        $params = Yii::$app->request->getQueryParams();

        if (\Yii::$app->request->get('searchString')) {
            $searchModel['searchString'] = trim(\Yii::$app->request->get('searchString'));
        }
        if (\Yii::$app->request->get('date_from')) {
            $searchModel['date_from'] = $searchModel->date_from = trim(\Yii::$app->request->get('date_from'));
        }
        if (\Yii::$app->request->get('date_to')) {
            $searchModel['date_to'] = $searchModel->date_to = trim(\Yii::$app->request->get('date_to'));
        }
        if (Yii::$app->request->post("RequestSearch")) {
            $params['RequestSearch'] = Yii::$app->request->post("RequestSearch");
        }
        $exportFilename = 'request_' . date("Y-m-d_H-m-s");
        $exportColumns = (new Request())->getRequestExportColumns();

        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial("request/index", compact('searchModel', 'dataProvider', 'exportFilename', 'exportColumns'));
        } else {
            return $this->render("request/index", compact('searchModel', 'dataProvider', 'exportFilename', 'exportColumns'));
        }
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionRequest($id)
    {
        if (!Request::find()->where(['id' => $id])->exists()) {
            return $this->redirect("list");
        }
        $request = Request::find()->where(['id' => $id])->one();
        $author = Organization::findOne(['id' => $request->rest_org_id]);
        $dataCallback = new ActiveDataProvider([
            'query'      => RequestCallback::find()->where(['request_id' => $id])->orderBy('id DESC'),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
        return $this->render("request/view", compact('request', 'author', 'dataCallback'));
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdateRequest($id)
    {
        $model = Request::find()->rightJoin('franchisee_associate', 'franchisee_associate.organization_id=request.rest_org_id')->where(['request.id' => $id])->andWhere(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['request', 'id' => $model->id]);
        } else {
            return $this->render('request/update', [
                'model' => $model,
            ]);
        }
    }

//    public function actionImportFromXls($id) {
//        $vendor = \common\models\Catalog::find()->where([
//                            'id' => $id,
//                            'type' => \common\models\Catalog::BASE_CATALOG
//                        ])
//                        ->one()
//                ->vendor;
//        $importModel = new \common\models\upload\UploadForm();
//        if (Yii::$app->request->isPost) {
//            $unique = 'article'; //уникальное поле
//            $sql_array_products = CatalogBaseGoods::find()->select($unique)->where([
//                        'cat_id' => $id,
//                        'deleted' => CatalogBaseGoods::DELETED_OFF
//                    ])->asArray()->all();
//            $count_array = count($sql_array_products);
//            $arr = [];
//            //массив артикулов из базы
//            for ($i = 0; $i < $count_array; $i++) {
//                array_push($arr, $sql_array_products[$i][$unique]);
//            }
//
//            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
//            $path = $importModel->upload();
//            if (!is_readable($path)) {
//                Yii::$app->session->setFlash('success', 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'
//                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
//                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
//                return $this->redirect(\Yii::$app->request->getReferrer());
//            }
//            $localFile = \PHPExcel_IOFactory::identify($path);
//            $objReader = \PHPExcel_IOFactory::createReader($localFile);
//            $objPHPExcel = $objReader->load($path);
//
//            $worksheet = $objPHPExcel->getSheet(0);
//            $highestRow = $worksheet->getHighestRow(); // получаем количество строк
//            $highestColumn = $worksheet->getHighestColumn(); // а так можно получить количество колонок
//            $newRows = 0;
//            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
//                $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
//                if (!in_array($row_article, $arr)) {
//                    $newRows++;
//                }
//            }
//            if ($newRows > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
//                Yii::$app->session->setFlash('success', 'Ошибка загрузки каталога<br>'
//                        . '<small>Вы пытаетесь загрузить каталог объемом больше ' . CatalogBaseGoods::MAX_INSERT_FROM_XLS . ' позиций (Новых позиций), обратитесь к нам и мы вам поможем'
//                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
//                return $this->redirect(\Yii::$app->request->getReferrer());
//            }
//            $transaction = Yii::$app->db->beginTransaction();
//            try {
//                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
//                    $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
//                    $row_product = trim($worksheet->getCellByColumnAndRow(1, $row)); //наименование
//                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
//                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
//                    $row_ed = trim($worksheet->getCellByColumnAndRow(4, $row)); //единица измерения
//                    $row_note = trim($worksheet->getCellByColumnAndRow(5, $row));  //Комментарий
//                    if (!empty($row_article && $row_product && $row_price && $row_ed)) {
//                        if (empty($row_units) || $row_units < 0) {
//                            $row_units = 0;
//                        }
//                        if (in_array($row_article, $arr)) {
//                            $CatalogBaseGoods = CatalogBaseGoods::find()->where([
//                                        'cat_id' => $id,
//                                        'article' => $row_article
//                                    ])->one();
//                            $CatalogBaseGoods->product = $row_product;
//                            $CatalogBaseGoods->units = $row_units;
//                            $CatalogBaseGoods->price = $row_price;
//                            $CatalogBaseGoods->ed = $row_ed;
//                            $CatalogBaseGoods->note = $row_note;
//                            $CatalogBaseGoods->es_status = CatalogBaseGoods::ES_UPDATE;
//                            $CatalogBaseGoods->save();
//                        } else {
//                            $CatalogBaseGoods = new CatalogBaseGoods();
//                            $CatalogBaseGoods->cat_id = $id;
//                            $CatalogBaseGoods->supp_org_id = $vendor->id;
//                            $CatalogBaseGoods->article = $row_article;
//                            $CatalogBaseGoods->product = $row_product;
//                            $CatalogBaseGoods->units = $row_units;
//                            $CatalogBaseGoods->price = $row_price;
//                            $CatalogBaseGoods->ed = $row_ed;
//                            $CatalogBaseGoods->note = $row_note;
//                            $CatalogBaseGoods->save();
//                        }
//                    }
//                }
//                $transaction->commit();
//                unlink($path);
//                return $this->redirect(['site/catalog', 'id' => $id]);
//            } catch (Exception $e) {
//                unlink($path);
//                $transaction->rollback();
//                Yii::$app->session->setFlash('success', 'Ошибка сохранения, повторите действие'
//                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
//                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
//            }
//        }
//
//        return $this->renderAjax('catalog/_importCatalog', compact('importModel'));
//    }

}
