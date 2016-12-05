<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
use common\models\User;
use common\models\Order;
use common\models\Organization;
use common\models\Delivery;
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
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * Controller for supplier
 */
class VendorController extends DefaultController {

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
                'only' => ['index', 'settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user', 'tutorial'],
                'rules' => [
//                    [
//                        
//                    ],
                    [
                        'actions' => ['settings', 'ajax-create-user', 'ajax-delete-user', 'ajax-update-user', 'ajax-validate-user'],
                        'allow' => true,
                        // Allow suppliers managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_FKEEPER_MANAGER,
                        ],
                    ],
                    [
                        'actions' => ['index', 'catalog', 'tutorial'],
                        'allow' => true,
                        // Allow suppliers managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
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

    public function actionSettings() {
        $organization = $this->currentUser->organization;

        if ($organization->load(Yii::$app->request->get())) {
            if ($organization->validate()) {
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

    public function actionDelivery() {
        $organization = $this->currentUser->organization;

        $delivery = $organization->delivery;
        if (!$delivery) {
            $delivery = new \common\models\Delivery();
            $delivery->vendor_id = $organization->id;
            $delivery->save();
        }

        if ($delivery->load(Yii::$app->request->get())) {
            if ($delivery->validate()) {
                $delivery->save();
            }
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('delivery', compact('delivery'));
        } else {
            return $this->render('delivery', compact('delivery'));
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

    public function actionCatalogs() {
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
            $searchString = "";
            $restaurant = "";
            $type = "";
            $relation_supp_rest = new RelationSuppRest;
            $relation = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                    where(['in', 'id', \common\models\RelationSuppRest::find()->
                                        select('rest_org_id')->
                                        where(['supp_org_id' => $currentUser->organization_id, 'invite' => '1'])])->all(), 'id', 'name');
            $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
                            where(['supp_org_id' => $currentUser->organization_id, 'type' => 2])->all();

            if (Yii::$app->request->isPost) {
                $searchString = htmlspecialchars(trim(\Yii::$app->request->post('searchString')));
                $restaurant = htmlspecialchars(trim(\Yii::$app->request->post('restaurant')));
                //echo $restaurant;
                if (!empty($restaurant)) {
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at', 'type', 'id'])->
                                    where(['supp_org_id' => $currentUser->organization_id])->
                                    andFilterWhere(['id' => \common\models\RelationSuppRest::find()->
                                        select(['cat_id'])->
                                        where(['supp_org_id' => $currentUser->organization_id,
                                            'rest_org_id' => $restaurant])])->one();
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
                    $arrCatalog = Catalog::find()->select(['id', 'status', 'name', 'created_at'])->
                                    where(['supp_org_id' => $currentUser->organization_id, 'type' => 2])->
                                    andFilterWhere(['LIKE', 'name', $searchString])->all();
                }
                return $this->render("catalogs", compact("relation_supp_rest", "currentUser", "relation", "searchString", "restaurant", 'arrCatalog', 'type'));
            }
            return $this->render("catalogs", compact("relation_supp_rest", "currentUser", "relation", "searchString", "restaurant", 'type', 'arrCatalog'));
        }
    }

    public function actionSupplierStartCatalogCreate() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);

            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            if ($arrCatalog === Array()) {
                $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Нельзя сохранить пустой каталог!']];
                return $result;
                exit;
            }
            //проверка на корректность введенных данных (цена)
            $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            foreach ($arrCatalog as $arrCatalogs) {
                $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                $ed = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
                $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                $category_name = htmlspecialchars(trim($arrCatalogs['dataItem']['category']));
                if (empty($article)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не указан <strong>Артикул</strong>']];
                    return $result;
                    exit;
                }
                if (empty($product)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не указано <strong>Наименование</strong>']];
                    return $result;
                    exit;
                }
                if (empty($price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не указана <strong>Цена</strong> продукта']];
                    return $result;
                    exit;
                }
                if (empty($ed)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не указана <strong>Единица измерения</strong> товара']];
                    return $result;
                    exit;
                }
                if (!empty($category_name)) {
                    if (!Category::find()->where(['name' => $category_name])->exists()) {
                        $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Ошибка в поле <strong>Категория</strong>']];
                        return $result;
                        exit;
                    }
                }
                $price = str_replace(',', '.', $price);

                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не верный формат <strong>Цены</strong><br><small>только число в формате 0,00</small>']];
                    return $result;
                    exit;
                }
                if (!empty($units) && !preg_match($numberPattern, $units)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не верный формат <strong>Кратность</strong><br><small>только число</small>']];
                    return $result;
                    exit;
                }
            }
            $sql = "insert into " . Catalog::tableName() . "(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($currentUser->organization_id,'Главный каталог'," . Catalog::BASE_CATALOG . ",NOW())";
            \Yii::$app->db->createCommand($sql)->execute();
            $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();

            foreach ($arrCatalog as $arrCatalogs) {
                $article = htmlspecialchars(trim($arrCatalogs['dataItem']['article']));
                $product = htmlspecialchars(trim($arrCatalogs['dataItem']['product']));
                $units = htmlspecialchars(trim($arrCatalogs['dataItem']['units']));
                $ed = htmlspecialchars(trim($arrCatalogs['dataItem']['ed']));
                $category_name = htmlspecialchars(trim($arrCatalogs['dataItem']['category']));
                $note = htmlspecialchars(trim($arrCatalogs['dataItem']['note']));
                if (empty($category_name)) {
                    $category_name = 0;
                } else {
                    //$category_name = 0;
                    $category_name = empty(Category::find()->where(["name" => $category_name])->one()->id) ? 0 : Category::find()->where(["name" => $category_name])->one()->id;
                }
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['price']));
                $price = str_replace(',', '.', $price);
                if (substr($price, -3, 1) == '.') {
                    $price = explode('.', $price);
                    $last = array_pop($price);
                    $price = join($price, '') . '.' . $last;
                } else {
                    $price = str_replace('.', '', $price);
                }

                $sql = "insert into {{%catalog_base_goods}}" .
                        "(`cat_id`,`supp_org_id`,`article`,`product`,"
                        . "`units`,`price`,`category_id`,`note`,`ed`,`status`,`market_place`,`deleted`,`created_at`) VALUES ("
                        . $lastInsert_base_cat_id . ","
                        . $currentUser->organization_id . ","
                        . ":article,"
                        . ":product,"
                        . ":units,"
                        . ":price,"
                        . ":category_id,"
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
                $command->bindParam(":category_id", $category_name);
                $command->bindParam(":note", $note, \PDO::PARAM_STR);
                $command->bindParam(":ed", $ed, \PDO::PARAM_STR);
                $command->execute();
                $lastInsert_base_goods_id = Yii::$app->db->getLastInsertID();
            }
            $result = ['success' => true, 'alert' => ['class' => 'success-fk', 'title' => 'Поздравляем!', 'body' => 'Вы успешно создали свой первый каталог!']];
            $currentOrganization = $currentUser->organization;
            if ($currentOrganization->step == Organization::STEP_ADD_CATALOG) {
                $currentOrganization->step = Organization::STEP_OK;
                $currentOrganization->save();
            }
            return $result;
            exit;
        }
    }

    public function actionClients() {
        $currentUser = User::findIdentity(Yii::$app->user->id);

        $arr_restaurant = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                where(['in', 'id', \common\models\RelationSuppRest::find()->
                                    select('rest_org_id')->
                                    where(['supp_org_id' => $currentUser->organization_id])])->all(), 'id', 'name');

        $arr_catalog = yii\helpers\ArrayHelper::map(\common\models\Catalog::find()->
                                where(['supp_org_id' => $currentUser->organization_id])->all(), 'id', 'name');

        $filter_restaurant = "";
        $filter_catalog = "";
        $filter_invite = "";
        $searchModel = new RelationSuppRest;
        if (
                !empty(\Yii::$app->request->get('filter_restaurant')) ||
                !empty(\Yii::$app->request->get('filter_catalog')) ||
                \Yii::$app->request->get('filter_invite') != "") {

            $filter_restaurant = trim(\Yii::$app->request->get('filter_restaurant'));
            $filter_catalog = trim(\Yii::$app->request->get('filter_catalog'));
            $filter_invite = trim(\Yii::$app->request->get('filter_invite'));

            $query = (new \yii\db\Query());
            $query->select("id,rest_org_id,cat_id,status,invite");
            $query->from("relation_supp_rest");
            $query->where(["supp_org_id" => $currentUser->organization_id]);
            if (!empty($filter_restaurant))
                $query->andWhere(["rest_org_id" => $filter_restaurant]);
            if (!empty($filter_catalog))
                $query->andWhere(["cat_id" => $filter_catalog]);
            if ($filter_invite != "")
                $query->andWhere(["invite" => $filter_invite]);
            /* $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM relation_supp_rest "
              . "WHERE supp_org_id = $currentUser->organization_id "
              //. "and id in (select id from organization where name like '" . $search . "%')"
              . "")->queryScalar(); */
        }else {
            $query = (new \yii\db\Query());
            $query->select("id,rest_org_id,cat_id,status,invite");
            $query->from("relation_supp_rest");
            $query->where(["supp_org_id" => $currentUser->organization_id]);
            /* $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM relation_supp_rest "
              . "WHERE supp_org_id = $currentUser->organization_id "
              . "")->queryScalar(); */
        }
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            //'totalCount' => $totalCount,
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
                    'status',
                ],
            ],
        ]);
        return $this->render('clients', compact('searchModel', 'dataProvider', 'arr_catalog', 'arr_restaurant'));
    }

    public function actionBasecatalog() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchString = "";
        $baseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG])->id;
        $currentCatalog = $baseCatalog;
        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
            $sql = "SELECT id,article,product,units,category_id,price,ed,note,status FROM catalog_base_goods "
                    . "WHERE cat_id = $baseCatalog AND "
                    . "deleted=0 AND (product LIKE :product or article LIKE :article)";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $baseCatalog AND "
                            . "deleted=0 AND (product LIKE :product or article LIKE :article)", [':article' => $searchString, ':product' => $searchString])->queryScalar();
        } else {
            $sql = "SELECT id,article,product,units,category_id,price,ed,note,status FROM catalog_base_goods "
                    . "WHERE cat_id = $baseCatalog AND "
                    . "deleted=0";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $baseCatalog AND "
                            . "deleted=0", [':article' => $searchString, ':product' => $searchString])->queryScalar();
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
                ],
            ],
        ]);
        $searchModel2 = new RelationSuppRest;
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        return $this->render('catalogs/basecatalog', compact('searchString', 'dataProvider', 'searchModel2', 'dataProvider2', 'currentCatalog'));
    }

    public function actionImportToXls($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            $unique = 'article'; //уникальное поле
            $sql_array_products = CatalogBaseGoods::find()->select($unique)->where(['cat_id' => $id, 'deleted' => 0])->asArray()->all();
            $count_array = count($sql_array_products);
            $arr = [];
            //массив артикулов из базы
            for ($i = 0; $i < $count_array; $i++) {
                array_push($arr, $sql_array_products[$i][$unique]);
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
                    $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
                    if (!in_array($row_article, $arr)) {
                    $newRows++;   
                    }
            }
            if ($newRows>5000) {
                Yii::$app->session->setFlash('success', 'Ошибка загрузки каталога<br>'
                        . '<small>Вы пытаетесь загрузить каталог объемом больше 1000 позиций (Новых позиций), обратитесь к нам и мы вам поможем'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }    
            $transaction = Yii::$app->db->beginTransaction();
            try {
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
                    $row_product = trim($worksheet->getCellByColumnAndRow(1, $row)); //наименование
                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                    $row_ed = trim($worksheet->getCellByColumnAndRow(4, $row)); //валюта
                    $row_note = trim($worksheet->getCellByColumnAndRow(5, $row)); //единица измерения
                    if (!empty($row_article && $row_product && $row_price && $row_ed)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        if (in_array($row_article, $arr)) {
                            $sql = "update {{%catalog_base_goods}} set "
                                    . "article=:article,"
                                    . "product=:product,"
                                    . "units=:units,"
                                    . "price=:price,"
                                    . "ed=:ed,"
                                    . "note=:note"
                                    . " where article='{$row_article}' and cat_id=$id";
                            $command = \Yii::$app->db->createCommand($sql);
                            $command->bindParam(":article", $row_article, \PDO::PARAM_STR);
                            $command->bindParam(":product", $row_product, \PDO::PARAM_STR);
                            $command->bindParam(":units", $row_units);
                            $command->bindParam(":price", $row_price);
                            $command->bindParam(":ed", $row_ed, \PDO::PARAM_STR);
                            $command->bindParam(":note", $row_note, \PDO::PARAM_STR);
                            $command->execute();
                        } else {
                            $sql = "insert into {{%catalog_base_goods}}" .
                                    "(`cat_id`,`category_id`,`supp_org_id`,`article`,`product`,"
                                    . "`units`,`price`,`ed`,`note`,`status`,`created_at`) VALUES ("
                                    . ":cat_id,"
                                    . "0,"
                                    . $currentUser->organization_id . ","
                                    . ":article,"
                                    . ":product,"
                                    . ":units,"
                                    . ":price,"
                                    . ":ed,"
                                    . ":note,"
                                    . CatalogBaseGoods::STATUS_ON . ","
                                    . "NOW())";
                            $command = \Yii::$app->db->createCommand($sql);
                            $command->bindParam(":cat_id", $id, \PDO::PARAM_INT);
                            $command->bindParam(":article", $row_article, \PDO::PARAM_STR);
                            $command->bindParam(":product", $row_product, \PDO::PARAM_STR);
                            $command->bindParam(":units", $row_units);
                            $command->bindParam(":price", $row_price);
                            $command->bindParam(":ed", $row_ed, \PDO::PARAM_STR);
                            $command->bindParam(":note", $row_note, \PDO::PARAM_STR);
                            $command->execute();
                        }
                    }
                }
                $transaction->commit();
                unlink($path);
                return $this->redirect(['vendor/basecatalog', 'id' => $id]);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('success', 'Ошибка сохранения, повторите действие'
                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
            }
        }
        
        return $this->renderAjax('catalogs/_importForm', compact('importModel'));
    }


    public function actionImportBaseCatalogFromXls() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
$importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            

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
            
            if ($highestRow>5000) {
                Yii::$app->session->setFlash('success', 'Ошибка загрузки каталога<br>'
                        . '<small>Вы пытаетесь загрузить каталог объемом больше 1000 позиций (Новых позиций), обратитесь к нам и мы вам поможем'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
                return $this->redirect(\Yii::$app->request->getReferrer());
            }  
              
            $transaction = Yii::$app->db->beginTransaction();
            try {
	            
	        $sql = "insert into " . Catalog::tableName() . "(`supp_org_id`,`name`,`type`,`created_at`) VALUES ($currentUser->organization_id,'Главный каталог'," . Catalog::BASE_CATALOG . ",NOW())";
            \Yii::$app->db->createCommand($sql)->execute();
            $lastInsert_base_cat_id = Yii::$app->db->getLastInsertID();
            
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_article = trim($worksheet->getCellByColumnAndRow(0, $row)); //артикул
                    $row_product = trim($worksheet->getCellByColumnAndRow(1, $row)); //наименование
                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                    $row_ed = trim($worksheet->getCellByColumnAndRow(4, $row)); //валюта
                    $row_note = trim($worksheet->getCellByColumnAndRow(5, $row)); //единица измерения
                    if (!empty($row_article && $row_product && $row_price && $row_ed)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                            $sql = "insert into {{%catalog_base_goods}}" .
                                    "(`cat_id`,`category_id`,`supp_org_id`,`article`,`product`,"
                                    . "`units`,`price`,`ed`,`note`,`status`,`created_at`) VALUES ("
                                    . ":cat_id,"
                                    . "0,"
                                    . $currentUser->organization_id . ","
                                    . ":article,"
                                    . ":product,"
                                    . ":units,"
                                    . ":price,"
                                    . ":ed,"
                                    . ":note,"
                                    . CatalogBaseGoods::STATUS_ON . ","
                                    . "NOW())";
                            $command = \Yii::$app->db->createCommand($sql);
                            $command->bindParam(":cat_id", $lastInsert_base_cat_id, \PDO::PARAM_INT);
                            $command->bindParam(":article", $row_article, \PDO::PARAM_STR);
                            $command->bindParam(":product", $row_product, \PDO::PARAM_STR);
                            $command->bindParam(":units", $row_units);
                            $command->bindParam(":price", $row_price);
                            $command->bindParam(":ed", $row_ed, \PDO::PARAM_STR);
                            $command->bindParam(":note", $row_note, \PDO::PARAM_STR);
                            $command->execute();
                        
                    }
                }
                $transaction->commit();
                unlink($path);
                return $this->redirect(['vendor/basecatalog', 'id' => $lastInsert_base_cat_id]);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('success', 'Ошибка сохранения, повторите действие'
                        . '<small>Если ошибка повторяется, пожалуйста, сообщите нам'
                        . '<a href="mailto://info@f-keeper.ru" target="_blank" class="alert-link" style="background:none">info@f-keeper.ru</a></small>');
            }
        }
       return $this->renderAjax('catalogs/_importCreateBaseForm', compact('importModel'));
       
    }

    public function actionChangestatus() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $Catalog = new Catalog;

            $id = \Yii::$app->request->post('id');
            $status = \Yii::$app->request->post('status');
            $status == 1 ? $status = 0 : $status = 1;
            $Catalog = Catalog::findOne(['id' => $id]);
            $Catalog->status = $status;
            $Catalog->update();

            $result = ['success' => true, 'status' => $status];
            return $result;
            exit;
        }
    }

    public function actionAjaxInviteRestOrgId() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $relationSuppRest = new RelationSuppRest;

            $id = \Yii::$app->request->post('id');
            $elem = \Yii::$app->request->post('elem');
            $state = \Yii::$app->request->post('state');
            if ($elem == 'restOrgId') {
                if ($state == 'true') {
                    $relationSuppRest = RelationSuppRest::findOne(['rest_org_id' => $id, 'supp_org_id' => $currentUser->organization_id]);
                    $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                    $relationSuppRest->update();

                    $result = ['success' => true, 'status' => 'update invite'];
                    return $result;
                } else {
                    $relationSuppRest = RelationSuppRest::findOne(['rest_org_id' => $id, 'supp_org_id' => $currentUser->organization_id]);
                    $relationSuppRest->invite = RelationSuppRest::INVITE_OFF;
                    $relationSuppRest->cat_id = RelationSuppRest::INVITE_OFF;
                    $relationSuppRest->update();

                    $result = ['success' => true, 'status' => 'no update invite'];
                    return $result;
                }
            }
        }
    }

    public function actionMycatalogdelcatalog() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $cat_id = \Yii::$app->request->post('id');

            $Catalog = Catalog::find()->where(['id' => $cat_id, 'type' => 2])->one();
            $Catalog->delete();

            $CatalogGoods = CatalogGoods::deleteAll(['cat_id' => $cat_id]);

            $RelationSuppRest = RelationSuppRest::updateAll(['cat_id' => null], ['cat_id' => $cat_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }

    public function actionAjaxDeleteProduct() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $product_id = \Yii::$app->request->post('id');
            $catalogBaseGoods = CatalogBaseGoods::updateAll(['deleted' => 1], ['id' => $product_id]);

            $result = ['success' => true];
            return $result;
            exit;
        }
    }

    /*
     *  User product
     */

    public function actionAjaxUpdateProduct($id) {
        $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();

            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                $catalogBaseGoods->supp_org_id = $currentUser->organization_id;
                if ($catalogBaseGoods->validate()) {

                    $catalogBaseGoods->save();

                    $message = 'Товар обновлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }

        return $this->renderAjax('catalogs/_baseProductForm', compact('catalogBaseGoods'));
    }
    public function actionAjaxUpdateProductMarketPlace($id) {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $currentOrgName = Organization::getOrganization($currentUser->organization)->name;
        $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
        //var_dump($catalogBaseGoods);
        $delivery = Delivery::find()->where(['vendor_id' => $currentUser->organization_id])->one();
        $categorys = new \yii\base\DynamicModel([
            'sub1','sub2'
        ]);
        /*$listSub2Categorys = \common\models\MpCategory::find()->where(['parent'=>
            \common\models\MpCategory::find()->where(['id'=>$catalogBaseGoods->category_id])->one()->parent
            ])->asArray()->all();*/
        $categorys->addRule(['sub1','sub2'], 'integer');
        
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post) && $categorys->load($post)) {
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                $catalogBaseGoods->supp_org_id = $currentUser->organization_id;
                if ($post && $catalogBaseGoods->validate()) {
                    $catalogBaseGoods->category_id = $categorys->sub2;
                    $catalogBaseGoods->save();
                    $message = 'Продукт обновлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_baseProductMarketPlaceForm', 
                compact('catalogBaseGoods','currentOrgName','delivery','categorys'));
    }
    public function actionGetSubCat() {   
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            $list = \common\models\MpCategory::find()->andWhere(['parent'=>$id])->asArray()->all();
            $selected  = null;
            if ($id != null && count($list) > 0) {
                $selected = '';
                foreach ($list as $i => $cat) {
                    $out[] = ['id' => $cat['id'], 'name' => $cat['name']];
                    if ($i == 0) {
                        $selected = $cat['id'];
                    }
                }
                // Shows how you can preselect a value
                echo Json::encode(['output' => $out, 'selected'=>$selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected'=>2]);
    }
    public function actionAjaxCreateProduct() {
        if (Yii::$app->request->isAjax) {
            $catalogBaseGoods = new CatalogBaseGoods();
            $currentUser = User::findIdentity(Yii::$app->user->id);
            
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->status = 1;

                if ($catalogBaseGoods->validate()) {
                    $catalogBaseGoods->supp_org_id = $currentUser->organization_id;
                    $catalogBaseGoods->save();

                    $message = 'Продукт добавлен!';
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_baseProductForm', compact('catalogBaseGoods'));
        
    }

    public function actionChangecatalogprop() {
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $CatalogBaseGoods = new CatalogBaseGoods;
            $id = \Yii::$app->request->post('id');
            $elem = \Yii::$app->request->post('elem');

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
                if ($CatalogBaseGoods->status == 0) {
                    $set = 1;
                } else {
                    $set = 0;
                }
                $CatalogBaseGoods->status = $set;
                $CatalogBaseGoods->update();

                $result = ['success' => true, 'status' => 'update status'];
                return $result;
            }
        }
    }

    public function actionChangesetcatalog() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $currentUser = User::findIdentity(Yii::$app->user->id);
            //$relation_supp_rest = new RelationSuppRest;
            $curCat = \Yii::$app->request->post('curCat'); //catalog
            $id = \Yii::$app->request->post('id'); //rest_org_id
            $state = Yii::$app->request->post('state');

            if ($state == 'true') {
                $rest_org_id = $id;
                $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                $relation_supp_rest->cat_id = $curCat;
                $relation_supp_rest->status = 1;
                $relation_supp_rest->update();

                return (['success' => true, 'Подписан']);
                exit;
            } else {
                $rest_org_id = $id;
                $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                $relation_supp_rest->status = 0;
                $relation_supp_rest->update();
                return (['success' => true, 'Не подписан']);
                exit;
            }
        }
    }

    public function actionChangecatalogstatus() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $id = \Yii::$app->request->post('id');
            $Catalog = Catalog::findOne(['id' => $id]);
            $Catalog->status = \Yii::$app->request->post('state') == 'true' ? 1 : 0;
            $Catalog->update();

            $result = ['success' => true, 'status' => 'update status'];
            return $result;
        }
    }

    public function actionCreateCatalog() {
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

    public function actionStep1() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $catalog = new Catalog();
            $post = Yii::$app->request->post();
            $currentUser = User::findIdentity(Yii::$app->user->id);
            if ($catalog->load($post)) {
                $catalog->supp_org_id = $currentUser->organization_id;
                $catalog->type = Catalog::CATALOG;
                $catalog->status = 1;
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Укажите корректное  <strong>Имя</strong> каталога']];
                    return $result;
                    exit;
                }
            } else {
                return (['success' => false, 'type' => 2, 'POST не определен']);
                exit;
            }
        }
        $catalog = new Catalog();
        return $this->render('newcatalog/step-1', compact('catalog'));
    }

    public function actionStep1Update($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $catalog = Catalog::find()->where(['id' => $cat_id])->one();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            if ($catalog->load($post)) {
                if ($catalog->validate()) {
                    $catalog->save();
                    return (['success' => true, 'cat_id' => $catalog->id]);
                } else {
                    $result = ['success' => false, 'type' => 1, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Укажите корректное  <strong>Имя</strong> каталога']];
                    return $result;
                    exit;
                }
            }
        }
        return $this->render('newcatalog/step-1', compact('catalog', 'cat_id', 'searchModel', 'dataProvider'));
    }

    public function actionStep1Clone($id) {
        $cat_id_old = $id; //id исходного каталога
        $currentUser = User::findIdentity(Yii::$app->user->id);

        $model = Catalog::findOne(['id' => $id]);
        $model->id = null;
        $model->name = $model->name . ' ' . date('H:i:s');
        $cat_type = $model->type;   //текущий тип каталога(исходный)    
        $model->type = Catalog::CATALOG; //переопределяем тип на 2
        $model->status = 1;
        $model->isNewRecord = true;
        $model->save();

        $cat_id = $model->id; //новый каталог id
        if ($cat_type == Catalog::BASE_CATALOG) {
            $sql = "insert into " . CatalogGoods::tableName() .
                    "(`cat_id`,`base_goods_id`,`price`,`created_at`) "
                    . "SELECT " . $cat_id . ", id, price, NOW() from " . CatalogBaseGoods::tableName() . " WHERE cat_id = $cat_id_old and deleted<>1";
            \Yii::$app->db->createCommand($sql)->execute();
        }
        if ($cat_type == Catalog::CATALOG) {
            $sql = "insert into " . CatalogGoods::tableName() .
                    "(`cat_id`,`base_goods_id`,`price`,`created_at`) "
                    . "SELECT " . $cat_id . ", base_goods_id, price, NOW() from " . CatalogGoods::tableName() . " WHERE cat_id = $cat_id_old";
            \Yii::$app->db->createCommand($sql)->execute();
        }

        return $this->redirect(['vendor/step-1-update', 'id' => $cat_id]);
    }

    public function actionStep2($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('check')) {
                if (CatalogGoods::find()->where(['cat_id' => $cat_id])->exists()) {
                    return (['success' => true, 'cat_id' => $cat_id]);
                } else {
                    return (['success' => false, 'type' => 1, 'message' => 'Пустой каталог']);
                    exit;
                }
            }
            if (Yii::$app->request->post('add-product')) {
                if (Yii::$app->request->post('state') == 'true') {

                    $product_id = Yii::$app->request->post('baseProductId');
                    $catalogGoods = new CatalogGoods;
                    $catalogGoods->base_goods_id = $product_id;
                    $catalogGoods->cat_id = $cat_id;
                    $catalogGoods->price = CatalogBaseGoods::findOne(['id' => $product_id])->price;
                    $catalogGoods->save();
                    return (['success' => true, 'Добавлен']);
                    exit;
                } else {
                    $product_id = Yii::$app->request->post('baseProductId');
                    $CatalogGoods = CatalogGoods::deleteAll(['base_goods_id' => $product_id]);
                    return (['success' => false, 'Удален']);
                    exit;
                }
            }
        }

        $baseCatalog = Catalog::findOne(['supp_org_id' => $currentUser->organization_id, 'type' => Catalog::BASE_CATALOG])->id;
        $searchString = "";
        if (!empty(trim(\Yii::$app->request->get('searchString')))) {
            $searchString = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
            $sql = "SELECT id,article,product,units,category_id,price,ed,status FROM catalog_base_goods "
                    . "WHERE cat_id = $baseCatalog AND "
                    . "deleted=0 AND (product LIKE :product or article LIKE :article)";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $baseCatalog AND "
                            . "deleted=0 AND (product LIKE :product or article LIKE :article)", [':article' => $searchString, ':product' => $searchString])->queryScalar();
        } else {
            $sql = "SELECT id,article,product,units,category_id,price,ed,status FROM catalog_base_goods "
                    . "WHERE cat_id = $baseCatalog AND "
                    . "deleted=0";
            $query = \Yii::$app->db->createCommand($sql);
            $totalCount = Yii::$app->db->createCommand("SELECT count(*) FROM catalog_base_goods "
                            . "WHERE cat_id = $baseCatalog AND "
                            . "deleted=0")->queryScalar();
        }
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'params' => [':article' => $searchString, ':product' => $searchString],
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'article',
                    'product',
                    'units',
                    'category_id',
                    'price',
                    'ed',
                    'status',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        return $this->render('newcatalog/step-2', compact('searchModel', 'dataProvider', 'cat_id'));
    }

    public function actionStep3Copy($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        // выборка для handsontable
        /* $arr = CatalogGoods::find()->select(['id', 'base_goods_id', 'price', 'discount', 'discount_percent'])->where(['cat_id' => $id])->
          andWhere(['not in', 'base_goods_id', CatalogBaseGoods::find()->select('id')->
          where(['supp_org_id' => $currentUser->organization_id, 'deleted' => 1])])->all();
          $arr = \yii\helpers\ArrayHelper::toArray($arr); */

        $sql = "SELECT "
                . "catalog.id as id,"
                . "article,"
                . "catalog_base_goods.product as product,"
                . "catalog_base_goods.id as base_goods_id,"
                . "catalog_goods.id as goods_id,"
                . "units,"
                . "ed,"
                . "catalog_base_goods.price as base_price,"
                . "catalog_goods.price as price,"
                . "catalog_base_goods.status"
                . " FROM `catalog` "
                . "LEFT JOIN catalog_goods on catalog.id = catalog_goods.cat_id "
                . "LEFT JOIN catalog_base_goods on catalog_goods.base_goods_id = catalog_base_goods.id "
                . "WHERE catalog.id = $id and catalog_base_goods.deleted != 1";
        $arr = \Yii::$app->db->createCommand($sql)->queryAll();

        $array = [];
        foreach ($arr as $arrs) {
            $c_article = $arrs['article'];
            $c_product = $arrs['product'];
            $c_base_goods_id = $arrs['base_goods_id'];
            $c_goods_id = $arrs['goods_id'];
            $c_base_price = $arrs['base_price'];
            $c_ed = $arrs['ed'];
            $c_price = $arrs['price'];

            array_push($array, [
                'article' => $c_article,
                'product' => $c_product,
                'base_goods_id' => $c_base_goods_id,
                'goods_id' => $c_goods_id,
                'base_price' => $c_base_price,
                'price' => $c_price,
                'ed' => $c_ed,
                'total_price' => $c_price]);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();
            $arrCatalog = json_decode(Yii::$app->request->post('catalog'), JSON_UNESCAPED_UNICODE);
            $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = htmlspecialchars(trim($arrCatalogs['dataItem']['goods_id']));
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['total_price']));

                if (!CatalogGoods::find()->where(['id' => $goods_id])->exists()) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не верный товар']];
                    return $result;
                    exit;
                }

                $price = str_replace(',', '.', $price);

                if (!preg_match($numberPattern, $price)) {
                    $result = ['success' => false, 'alert' => ['class' => 'danger-fk', 'title' => 'УПС! Ошибка', 'body' => 'Не верный формат <strong>Цены</strong><br><small>только число в формате 0,00</small>']];
                    return $result;
                    exit;
                }
            }
            foreach ($arrCatalog as $arrCatalogs) {
                $goods_id = htmlspecialchars(trim($arrCatalogs['dataItem']['goods_id']));
                $price = htmlspecialchars(trim($arrCatalogs['dataItem']['total_price']));

                $price = str_replace(',', '.', $price);

                $catalogGoods = CatalogGoods::findOne(['id' => $goods_id]);
                $catalogGoods->price = $price;
                $catalogGoods->update();
            }
            $result = ['success' => true, 'alert' => ['class' => 'success-fk', 'title' => 'Сохранено', 'body' => 'Данные успешно обновлены']];
            return $result;
            exit;
        }
        return $this->render('newcatalog/step-3-copy', compact('array', 'cat_id'));
    }

    public function actionStep3($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchModel = new CatalogGoods();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $cat_id);
        return $this->render('newcatalog/step-3', compact('searchModel', 'dataProvider', 'exportModel'));
    }

    public function actionStep3UpdateProduct($id) {
        $catalogGoods = CatalogGoods::find()->where(['id' => $id])->one();
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

    public function actionStep4($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $searchModel = new RelationSuppRest;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $currentUser, RelationSuppRest::PAGE_CATALOG);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('add-client')) {
                if (Yii::$app->request->post('state') == 'true') {
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                    $relation_supp_rest->cat_id = $cat_id;
                    $relation_supp_rest->status = 1;
                    $relation_supp_rest->update();

                    return (['success' => true, 'Подписан']);
                    exit;
                } else {
                    $rest_org_id = Yii::$app->request->post('rest_org_id');
                    $relation_supp_rest = RelationSuppRest::findOne(['rest_org_id' => $rest_org_id, 'supp_org_id' => $currentUser->organization_id]);
                    $relation_supp_rest->cat_id = Catalog::NON_CATALOG;
                    $relation_supp_rest->status = 0;
                    $relation_supp_rest->update();
                    return (['success' => true, 'Не подписан']);
                    exit;
                }
            }
        }
        return $this->render('newcatalog/step-4', compact('searchModel', 'dataProvider', 'currentCatalog', 'cat_id'));
    }

    public function actionAjaxAddClient() {
        $user = new User(['scenario' => 'sendInviteFromVendor']);
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($user->load($post)) {
                if ($user->validate()) {
                    $this->currentUser->sendInviteToClient($user);
                    $message = 'Приглашение отправлено!';
                    return $this->renderAjax('clients/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('clients/_addClientForm', compact('user'));
    }

    public function actionAjaxSetPercent($id) {
        $cat_id = $id;
        $catalogGoods = new CatalogGoods(['scenario' => 'update']);
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $catalogGoods->cat_id = $cat_id;
            if ($catalogGoods->load($post)) {
                if ($catalogGoods->validate()) {

                    $catalogGoods = CatalogGoods::updateAll(['price' => 'price' - (('price' / 100) * $catalogGoods->discount_percent)], ['cat_id' => $cat_id]);
                    $message = "Сохранено!";
                    return $this->renderAjax('catalogs/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('catalogs/_setPercentCatalog', compact('catalogGoods', 'cat_id'));
    }

    public function actionViewClient($id) {
        $client_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $organization = Organization::find()->where(['id' => $client_id])->one();
        $relation_supp_rest = RelationSuppRest::find()->where([
                    'rest_org_id' => $client_id,
                    'supp_org_id' => $currentUser->organization_id])->one();
        $catalogs = \yii\helpers\ArrayHelper::map(Catalog::find()->
                                where(['supp_org_id' => $currentUser->organization_id])->
                                all(), 'id', 'name');
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($relation_supp_rest->load($post)) {
                if ($relation_supp_rest->validate()) {

                    $relation_supp_rest->update();
                    $message = 'Сохранено';
                    return $this->renderAjax('clients/_success', ['message' => $message]);
                }
            }
        }
        return $this->renderAjax('clients/_viewClient', compact('organization', 'relation_supp_rest', 'catalogs', 'client_id'));
    }

    public function actionViewCatalog($id) {
        $cat_id = $id;
        $currentUser = User::findIdentity(Yii::$app->user->id);
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::BASE_CATALOG) {
            $searchModel = new CatalogBaseGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id, NULL);
            return $this->renderAjax('catalogs/_viewBaseCatalog', compact('searchModel', 'dataProvider', 'cat_id'));
        }
        if (Catalog::find()->where(['id' => $cat_id])->one()->type == Catalog::CATALOG) {
            $searchModel = new CatalogGoods;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);
            return $this->renderAjax('catalogs/_viewCatalog', compact('searchModel', 'dataProvider', 'cat_id'));
        }
    }

    public function actionListCatalog() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $search = Yii::$app->request->post('search');
        $restaurant = Yii::$app->request->post('restaurant');

        return $this->renderAjax('catalogs/_listCatalog', compact('currentUser', 'search', 'restaurant'));
    }

    public function actionMessages() {
        return $this->render('/site/underConstruction');
    }

    public function actionEvents() {
        return $this->render('/site/underConstruction');
    }

    public function actionAnalytics() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        $header_info_zakaz = \common\models\Order::find()->
                        where(['vendor_id' => $currentUser->organization_id])->count();        
        empty($header_info_zakaz) ? $header_info_zakaz = 0 : $header_info_zakaz = (int)$header_info_zakaz;
        $header_info_clients = \common\models\RelationSuppRest::find()->
                        where(['supp_org_id' => $currentUser->organization_id])->count();
        empty($header_info_clients) ? $header_info_clients = 0 : $header_info_clients = (int)$header_info_clients;
        $header_info_prodaji = \common\models\Order::find()->
                        where(['vendor_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])->count();
        empty($header_info_prodaji) ? $header_info_prodaji = 0 : $header_info_prodaji = (int)$header_info_prodaji;
        $header_info_poziciy = \common\models\OrderContent::find()->select('sum(quantity) as quantity')->
                        where(['in', 'order_id', \common\models\Order::find()->select('id')->where(['vendor_id' => $currentUser->organization_id, 'status' => \common\models\Order::STATUS_DONE])])->one()->quantity;
        empty($header_info_poziciy) ? $header_info_poziciy = 0 : $header_info_poziciy = (int)$header_info_poziciy;
        $filter_restaurant = yii\helpers\ArrayHelper::map(\common\models\Organization::find()->
                                where(['in', 'id', \common\models\RelationSuppRest::find()->
                                    select('rest_org_id')->
                                    where(['supp_org_id' => $currentUser->organization_id, 'invite' => '1'])])->all(), 'id', 'name');
        $filter_status = "";
        $filter_from_date = date("d-m-Y", strtotime(" -2 months"));
        $filter_to_date = date("d-m-Y");
        $filter_client = "";
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
            $filter_from_date = trim(\Yii::$app->request->get('filter_from_date'));
            $filter_to_date = trim(\Yii::$app->request->get('filter_to_date'));
            $filter_client = trim(\Yii::$app->request->get('filter_client'));

            empty($filter_status) ? "" : $where .= " and status='" . $filter_status . "'";
            empty($filter_client) ? "" : $where .= " and client_id='" . $filter_client . "'";
        }
        // Объем продаж чарт
        $area_chart = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
                (select sum(total_price) FROM `order` 
                where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at and 
                vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and ("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "')" .
                        $where .
                        ") AS `total_price`  
                FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS `created_at` 
                FROM `order` where 
                vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "')" . $where . ")`tb`")->queryAll();
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

        $query = Yii::$app->db->createCommand("
            SELECT sum(price*quantity) as price, product_id FROM order_content WHERE order_id in (
                SELECT id from `order` where 
                (DATE(created_at) between '" .
                date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "')" .
                "and status<>" . Order::STATUS_FORMING . " and vendor_id = " . $currentUser->organization_id .
                $where .
                ") group by product_id");
        $totalCount = Yii::$app->db->createCommand("
            SELECT COUNT(*) from (
            SELECT sum(price*quantity) as price, product_id FROM order_content WHERE order_id in (
                SELECT id from `order` where 
                (DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "')" .
                        "and status<>" . Order::STATUS_FORMING . " and vendor_id = " . $currentUser->organization_id .
                        $where .
                        ") group by product_id)tb")->queryScalar();
        $total_price = Yii::$app->db->createCommand("SELECT sum(total_price) as total from `order` where " .
                        "vendor_id = " . $currentUser->organization_id .
                        " and status<>" . Order::STATUS_FORMING . " and DATE_FORMAT(created_at,'%Y-%m-%d') between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "'" . $where)->queryOne();
        $total_price = $total_price['total'];
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 7,
            ],
            'sort' => [
                'attributes' => [
                    'product_id',
                    'price'
                ],
                'defaultOrder' => [
                    'price' => SORT_DESC
                ]
            ],
        ]);

        $clients_query = Yii::$app->db->createCommand("
            SELECT client_id,sum(total_price) as total_price FROM `order` WHERE  
                (DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" . date('Y-m-d', strtotime($filter_to_date)) . "') " .
                        $where .
                        " and vendor_id = " . $currentUser->organization_id .
                        " and status<>" . Order::STATUS_FORMING . " group by client_id")->queryAll();
        $arr_clients_price = [];
        foreach ($clients_query as $clients_querys) {
            $arr = array(
                'value' => $clients_querys['total_price'],
                'label' => \common\models\Organization::find()->where(['id' => $clients_querys['client_id']])->one()->name,
                'color' => hex()
            );
            array_push($arr_clients_price, $arr);
        }
        $arr_clients_price = json_encode($arr_clients_price);

        return $this->render('analytics/index', compact('filter_restaurant', 'header_info_zakaz', 'header_info_clients', 'header_info_prodaji', 'header_info_poziciy', 'filter_status', 'filter_from_date', 'filter_to_date', 'filter_client', 'arr_create_at', 'arr_price', 'dataProvider', 'arr_clients_price', 'total_price'
        ));
    }

    /*
     *  index
     */

    public function actionIndex() {
        $currentUser = User::findIdentity(Yii::$app->user->id);
        //ГРАФИК ПРОДАЖ -----> 
        $filter_from_date = date("d-m-Y", strtotime(" -1 months"));
        $filter_to_date = date("d-m-Y");
        $area_chart = Yii::$app->db->createCommand("SELECT DATE_FORMAT(created_at,'%d-%m-%Y') as created_at,
            (select sum(total_price) FROM `order` 
            where DATE_FORMAT(created_at,'%Y-%m-%d') = tb.created_at and 
            vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and ("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "')" .
                        ") AS `total_price`  
            FROM (SELECT distinct(DATE_FORMAT(created_at,'%Y-%m-%d')) AS `created_at` 
            FROM `order` where 
            vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "'))`tb`")->queryAll();
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
        // <------ГРАФИК ПРОДАЖ
        //------>Статистика 
        $stats = Yii::$app->db->createCommand("SELECT
            (SELECT sum(total_price) FROM `order`
            WHERE vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and DATE_FORMAT(created_at, '%Y-%m-%d') = CURDATE()) as 'curDay',
            (SELECT sum(total_price) FROM `order` 
             WHERE vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and (MONTH(`created_at`) = MONTH(NOW()) AND YEAR(`created_at`) = YEAR(NOW()))) 
            as 'curMonth',
            (SELECT sum(total_price) FROM `order` 
            WHERE vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and YEAR(`created_at`) = YEAR(NOW()) AND WEEK(`created_at`, 1) = WEEK(NOW(), 1))
             as 'curWeek',
            (SELECT sum(total_price) FROM `order` 
            WHERE vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and MONTH(`created_at`) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(`created_at`) = YEAR(NOW()))
            as 'lastMonth',
            (SELECT sum(total_price) FROM `order` 
            WHERE vendor_id = $currentUser->organization_id and status<>" . Order::STATUS_FORMING . " and MONTH(`created_at`) = MONTH(DATE_ADD(NOW(), INTERVAL -2 MONTH)) AND YEAR(`created_at`) = YEAR(NOW()))
            as 'TwoLastMonth'")->queryOne();
        // <-------Статистика 
        //GRIDVIEW ИСТОРИЯ ЗАКАЗОВ ----->
        $query = Yii::$app->db->createCommand("SELECT id,client_id,vendor_id,created_by_id,accepted_by_id,status,total_price,created_at FROM `order` WHERE "
                . "vendor_id = $currentUser->organization_id and ("
                . "DATE(created_at) between '" .
                date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                date('Y-m-d', strtotime($filter_to_date)) . "') and status<>" . Order::STATUS_FORMING);
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM (SELECT id,client_id,vendor_id,created_by_id,accepted_by_id,status,total_price,created_at FROM `order` WHERE "
                        . "vendor_id = $currentUser->organization_id and ("
                        . "DATE(created_at) between '" .
                        date('Y-m-d', strtotime($filter_from_date)) . "' and '" .
                        date('Y-m-d', strtotime($filter_to_date)) . "') and status<>" . Order::STATUS_FORMING . ")`tb`")->queryScalar();
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => $query->sql,
            'totalCount' => $totalCount,
            'pagination' => [
                'pageSize' => 10,
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

        return $this->render('dashboard/index', compact(
                                'dataProvider', 'filter_from_date', 'filter_to_date', 'arr_create_at', 'arr_price', 'stats'
        ));
    }

    public function actionTutorial() {
        return $this->render('tutorial');
    }

    public function actionSupport() {
        return $this->render('/site/underConstruction');
    }

    public function actionSidebar() {
        Yii::$app->session->get('sidebar-collapse') ?
                        Yii::$app->session->set('sidebar-collapse', false) :
                        Yii::$app->session->set('sidebar-collapse', true);
    }

}
