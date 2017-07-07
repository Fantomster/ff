<?php

namespace franchise\controllers;

use Yii;
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
class SiteController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

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
                'only' => ['index','setting', 'service-desk', 'settings', 'promotion', 'users', 'create-user', 'update-user', 'delete-user', 'validate-user', 'catalog', 'get-sub', 'import-from-xls', 'ajax-delete-product', 'ajax-edit-catalog-form'],
                'rules' => [
                    [
                        'actions' => ['index','setting','setting', 'service-desk', 'settings', 'promotion', 'users', 'create-user', 'update-user', 'delete-user', 'validate-user', 'catalog', 'get-sub', 'import-from-xls', 'ajax-delete-product', 'ajax-edit-catalog-form'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
//             'denyCallback' => function($rule, $action) {
//              throw new \yii\web\HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
//              } 
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
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
    public function actionIndex() {

        //---graph start
        $query = "SELECT truncate(sum(total_price),1) as spent, year(created_at) as year, month(created_at) as month, day(created_at) as day "
                . "FROM `order` LEFT JOIN `franchisee_associate` ON `order`.vendor_id = `franchisee_associate`.organization_id "
                . "where status in (" . Order::STATUS_PROCESSING . "," . Order::STATUS_DONE . "," . Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT . "," . Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR . ") "
                . "and created_at BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() + INTERVAL 1 DAY AND `franchisee_associate`.franchisee_id = " . $this->currentFranchisee->id . " "
                . "group by year(created_at), month(created_at), day(created_at)";
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
        $vendorsStats = $this->currentFranchisee->getMyVendorsStats();

        $params = Yii::$app->request->getQueryParams();
        $searchModel = new \franchise\models\OrderSearch();
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, true);

        $franchiseeType = $this->currentFranchisee->type;

        return $this->render('index', compact('dataProvider', 'dayLabels', 'dayTurnover', 'total30Count', 'totalCount', 'clientsCount', 'vendorsCount', 'vendorsStats30', 'vendorsStats', 'franchiseeType'));
    }
    
    
    public function actionSettings() {
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
    public function actionOrders() {
        $searchModel = new \franchise\models\OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = "01.02.2017";

        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderSearch")) {
            $params['OrderSearch'] = Yii::$app->request->post("OrderSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact('searchModel', 'dataProvider'));
        } else {
            return $this->render('orders', compact('searchModel', 'dataProvider'));
        }
    }
    public function actionServiceDesk() {
        $model = new ServiceDesk();
        $franchise = $this->currentFranchisee;
        $franchise_user = $this->currentUser->profile;
        if ($model->load(Yii::$app->request->post())) {

            $model->region = '';
            $model->phone = str_replace("+", "", $franchise_user->phone);
            $model->fio = $franchise_user->full_name;
            
          if($model->validate()){
            $objClientAuth  = new \Google_Client();
            $objClientAuth -> setApplicationName (CLIENT_APP_NAME);
            $objClientAuth -> setClientId (SERVICE_ACCOUNT_CLIENT_ID);
            
            $objClientAuth -> setAssertionCredentials (new \Google_Auth_AssertionCredentials (
                SERVICE_ACCOUNT_EMAIL, 
                array('https://spreadsheets.google.com/feeds','https://docs.google.com/feeds'), 
                file_get_contents (SERVICE_ACCOUNT_PKCS12_FILE_PATH), 
                CLIENT_KEY_PW
            ));
            
            $objClientAuth->getAuth()->refreshTokenWithAssertion();
            $objToken  = json_decode($objClientAuth->getAccessToken());
            
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
                'fid' => $franchise->id,
                'fuid' => $franchise_user->user_id,
                'region' => $model->region,
                'fio' => $model->fio,
                'phone' => $model->phone,
                'priority' => $model->priority,
                'message' => $model->body,
                'startdatetime' => date("Y-m-d H:i:s")
            ]);  
          }
        }
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('service-desk',[
                'model' => $model,
                ]);
        } else {
            return $this->render('service-desk',[
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
    public function actionUsers() {
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
        $organizationType = Organization::TYPE_FRANCHISEE;

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                $profile->load($post);

                if ($user->validate() && $profile->validate()) {

                    $user->setRegisterAttributes($user->role_id)->save();
                    $profile->setUser($user->id)->save();
                    $user->setFranchisee($this->currentFranchisee->id);
//                    $this->currentUser->sendEmployeeConfirmation($user);
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
        $user = User::find()
                ->joinWith("franchiseeUser")
                ->where([
                    'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                    'user.id' => $id
                ])
                ->one();
        $user->setScenario("manage");
        $profile = $user->profile;
        $organizationType = Organization::TYPE_FRANCHISEE;

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
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($post && isset($post['id'])) {
                $user = $user = User::find()
                        ->joinWith("franchiseeUser")
                        ->where([
                            'franchisee_user.franchisee_id' => $this->currentFranchisee->id,
                            'user.id' => $post["id"],
                        ])
                        ->one();
                $usersCount = count($this->currentFranchisee->franchiseeUsers);
                if ($user->id == $this->currentUser->id) {
                    $message = 'Может воздержимся от удаления себя?';
                    return $this->renderAjax('settings/_success', ['message' => $message]);
                }
                if ($user && ($usersCount > 1)) {
                    $user->role_id = Role::ROLE_USER;
                    $user->organization_id = null;
                    if ($user->save() && $user->franchiseeUser->delete()) {
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
     * Displays promotion
     * 
     * @return mixed
     */
    public function actionPromotion() {
        return $this->render('promotion');
    }

    public function actionCatalog($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchString = "";
        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
//            
//            $count = \common\models\CatalogBaseGoods::find()
//            ->where([
//            'cat_id'=>$id, 
//            'deleted'=>\common\models\CatalogBaseGoods::DELETED_OFF
//            ])
//            ->andWhere(['like','product',$searchString])
//            ->count();
//            
            $sql = "SELECT id,cat_id,article,product,units,category_id,price,ed,note,status,market_place FROM catalog_base_goods "
                    . "WHERE cat_id = $id AND "
                    . "deleted=0 AND (product LIKE :product or article LIKE :article)";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $id AND "
                            . "deleted=" . CatalogBaseGoods::DELETED_OFF . " AND (product LIKE :product or article LIKE :article)", [':article' => $searchString, ':product' => $searchString])->queryScalar();
        } else {
            $sql = "SELECT id,article,cat_id,product,units,category_id,price,ed,note,status,market_place FROM catalog_base_goods "
                    . "WHERE cat_id = $id AND "
                    . "deleted=" . CatalogBaseGoods::DELETED_OFF;
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $id AND "
                            . "deleted=" . CatalogBaseGoods::DELETED_OFF, [':article' => $searchString, ':product' => $searchString])->queryScalar();
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'params' => [':article' => $searchString, ':product' => $searchString],
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'note',
                    'status',
                    'cat_id'
                ],
            ],
        ]);
        return $this->render('catalog', compact('searchString', 'dataProvider', 'id'));
    }

    public function actionAjaxEditCatalogForm($catalog = null) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $catalog = isset(Yii::$app->request->get()['catalog']) ?
                Yii::$app->request->get()['catalog'] :
                Yii::$app->request->post()['catalog'];
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
                $catalogBaseGoods->status = CatalogBaseGoods::STATUS_ON;
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                if ($post && $catalogBaseGoods->validate()) {
                    $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                    $catalogBaseGoods->save();

                    $message = 'Продукт обновлен!';
                    return $this->renderAjax('catalog/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalog/_ajaxEditCatalogForm', compact('catalogBaseGoods', 'countrys', 'catalog'));
    }

    public function actionGetSubCat() {
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

    public function actionAjaxDeleteProduct() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll([
                        'deleted' => CatalogBaseGoods::DELETED_ON,
                        'es_status' => CatalogBaseGoods::ES_DELETED
                            ], ['id' => $product_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }
    public function actionImportFromXls($id){
        set_time_limit(180);
        $vendor = \common\models\Catalog::find()->where([
                    'id' => $id,
                    'type' => \common\models\Catalog::BASE_CATALOG
                ])
                ->one()
        ->vendor;
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
                Yii::$app->session->setFlash('success', 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'
                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }
            $localFile = \PHPExcel_IOFactory::identify($path);
            $objReader = \PHPExcel_IOFactory::createReader($localFile);
            $objPHPExcel = $objReader->load($path);

            $worksheet = $objPHPExcel->getSheet(0);
            $highestRow = $worksheet->getHighestRow(); // получаем количество строк
            $highestColumn = $worksheet->getHighestColumn(); // а так можно получить количество колонок
            $newRows = 0;
            for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                $row_article = trim($worksheet->getCellByColumnAndRow(0, $row));
                if (!in_array($row_article, $arr)) {
                    $newRows++;
                }
            }
            if ($newRows > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', 'Ошибка загрузки каталога<br>'
                        . '<small>Вы пытаетесь загрузить каталог объемом больше ' . CatalogBaseGoods::MAX_INSERT_FROM_XLS . ' позиций (Новых позиций), обратитесь к нам и мы вам поможем'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
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
                            $data_insert[] = [
                                $id, 
                                $vendor->id, 
                                $row_article,
                                $row_product,
                                $row_units,
                                $row_price,
                                $row_ed,
                                $row_note,
                                CatalogBaseGoods::STATUS_ON
                                ];
                        }else{
                                $CatalogBaseGoods = CatalogBaseGoods::find()->where([
                                        'cat_id' => $id,
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
                if(!empty($data_insert)){
                $db = Yii::$app->db;
                $sql = $db->queryBuilder->batchInsert(CatalogBaseGoods::tableName(), [
                    'cat_id','supp_org_id','article','product','units','price','ed','note','status'
                    ], $data_insert);
                Yii::$app->db->createCommand($sql)->execute();
                }
                $transaction->commit();
                unlink($path);
                return $this->redirect(['site/catalog', 'id' => $id]);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('success', 'Ошибка сохранения, повторите действие'
                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
            }
        }
        return $this->renderAjax('catalog/_importCatalog', compact('importModel'));
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
//                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
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
//                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
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
//                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
//            }
//        }
//
//        return $this->renderAjax('catalog/_importCatalog', compact('importModel'));
//    }

}
