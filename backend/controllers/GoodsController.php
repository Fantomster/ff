<?php

namespace backend\controllers;

use common\models\MpCategory;
use common\models\MpCountry;
use Yii;
use common\models\CatalogBaseGoods;
use common\models\Role;
use common\models\RelationSuppRest;
use common\models\Catalog;
use common\models\CatalogGoods;
use backend\models\CatalogBaseGoodsSearch;
use backend\models\CatalogBaseGoodsSetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * GoodsController implements the CRUD actions for CatalogBaseGoods model.
 */
class GoodsController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['ajax-clear-category', 'ajax-set-category', 'ajax-clear-category-multi', 'ajax-set-category-multi', 'ajax-update-product-market-place', 'import-catalog', 'import'],
                        'allow'   => true,
                        'roles'   => [Role::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['index', 'vendor', 'view', 'category', 'get-sub-cat', 'mp-country', 'uploaded-catalogs'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CatalogBaseGoods models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CatalogBaseGoodsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionVendor($id)
    {
        $searchModel = new CatalogBaseGoodsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $dataProvider->query->andWhere(['supp_org_id' => $id]);
        $isEditable = true;
        return $this->render('vendor', compact('id', 'searchModel', 'dataProvider', 'isEditable'));
    }

    public function actionAjaxUpdateProductMarketPlace($id, $cat_id = null, $supp_org_id = null)
    {
        if ($id) {
            $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
        } else {
            $catalogBaseGoods = new CatalogBaseGoods();
        }
        $catalogBaseGoods->scenario = 'marketPlace';
        $arrayOne = MpCountry::find()->select(['id', 'name'])->where(['LIKE', 'name', 'Россия'])->asArray()->all();
        $arrayTwo = MpCountry::find()->select(['id', 'name'])->where(['<>', 'name', 'Россия'])->asArray()->all();
        $countrys = array_merge($arrayOne, $arrayTwo);

        if (!empty($catalogBaseGoods->category_id)) {
            $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
            $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
        }

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                if ($id)
                    $checkBaseGood = CatalogBaseGoods::find()->where(['cat_id' => $catalogBaseGoods->cat_id, 'product' => $catalogBaseGoods->product])->andWhere(['not in', 'id', [$catalogBaseGoods->id]])->all();
                else
                    $checkBaseGood = CatalogBaseGoods::findAll(['cat_id' => $catalogBaseGoods->cat_id, 'product' => $catalogBaseGoods->product, 'deleted' => 0]);

                if ($checkBaseGood) {
                    $message = Yii::t('error', 'frontend.controllers.vendor.cat_error_five_two');
                    return $this->renderAjax('_success', ['message' => $message]);
                }
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                //$catalogBaseGoods->supp_org_id = $supp_org_id;

                if ($catalogBaseGoods->market_place == 1) {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status = 1;
                        $catalogBaseGoods->save();
                        $message = Yii::t('app', 'Товар обновлен!');

                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                } else {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status = 2;
                        $catalogBaseGoods->save();

                        $message = Yii::t('app', 'Товар обновлен!');
                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('_form', compact('catalogBaseGoods', 'countrys', 'supp_org_id'));
    }

    public function actionMpCountryList($q)
    {
        if (Yii::$app->request->isAjax) {
            $model = new \common\models\MpCountry();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $model->ajaxsearch($q);
        }
        return false;
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

    public function actionCategory($vendor_id, $id = 0)
    {
        $vendor = \common\models\Organization::findOne(['id' => $vendor_id]);

        $searchSetModel = new CatalogBaseGoodsSetSearch();

        $dataProviderCategory = $searchSetModel->search(Yii::$app->request->queryParams);
        $dataProviderCategory->query->andWhere(['category_id' => $id, 'supp_org_id' => $vendor_id]);

        $searchModel = new CatalogBaseGoodsSearch();
        $dataProviderEmpty = $searchModel->search(Yii::$app->request->queryParams);
        $dataProviderEmpty->query->andWhere(['supp_org_id' => $vendor_id]);
        $dataProviderEmpty->query->andWhere('(category_id is null) OR (category_id = 0)');

        $subCategory = MpCategory::findOne(['id' => $id]);
        if ($subCategory === null)
            $subCategory = new MpCategory();
        $category = MpCategory::findOne(['id' => $subCategory->parent]);
        if ($category === null)
            $category = new MpCategory();

        return $this->render('category', compact('id', 'dataProviderCategory', 'dataProviderEmpty', 'vendor', 'subCategory', 'category', 'searchModel', 'searchSetModel'));
    }

    public function actionAjaxClearCategory()
    {
        $post = Yii::$app->request->post();
        if ($post) {
            $product = CatalogBaseGoods::findOne(['id' => $post['id']]);
            $product->category_id = null;
            return $product->save(false);
        }
        return false;
    }

    public function actionAjaxSetCategoryMulti()
    {
        $post = Yii::$app->request->post();
        if ($post) {
            Yii::$app->db->createCommand()
                ->update(CatalogBaseGoods::tableName(), ['category_id' => $post['category_id']], ['in', 'id', $post['pk']])
                ->execute();
            return true;
        }
        return false;
    }

    public function actionAjaxClearCategoryMulti()
    {
        $post = Yii::$app->request->post();
        if ($post) {
            Yii::$app->db->createCommand()
                ->update(CatalogBaseGoods::tableName(), ['category_id' => null], ['in', 'id', $post['pk']])
                ->execute();
            return true;
        }
        return false;
    }

    public function actionAjaxSetCategory()
    {
        $post = Yii::$app->request->post();
        if ($post) {
            $product = CatalogBaseGoods::findOne(['id' => $post['id']]);
            $product->category_id = $post['category_id'];
            return $product->save(false);
        }
        return false;
    }

    public function actionUploadedCatalogs()
    {
        $query = RelationSuppRest::find()->where(['is not', 'uploaded_catalog', null])->andWhere(['uploaded_processed' => RelationSuppRest::UPLOADED_NOT_PROCESSED]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->render("uploaded-catalogs", compact("dataProvider"));
    }

    public function actionImport($id)
    {
        $currentUser = \common\models\User::findIdentity(Yii::$app->user->id);
        $importModel = new \common\models\upload\UploadForm();
        $vendor_id = $id;
        $id = Catalog::findOne(['supp_org_id' => $id, 'type' => 1])->id;

        if (Yii::$app->request->isPost) {
            $importType = \Yii::$app->request->post('UploadForm')['importType'];
            $sql_array_products = CatalogBaseGoods::find()->select(['id', 'product'])->where(['cat_id' => $id, 'deleted' => 0])->asArray()->all();
            $arr = \yii\helpers\ArrayHelper::map($sql_array_products, 'id', 'product');
            unset($sql_array_products);
            $arr = array_map('mb_strtolower', $arr);
            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.cat_error', ['ru' => 'Ошибка загрузки файла, посмотрите инструкцию по загрузке каталога<br>'])
                    . Yii::t('error', 'frontend.controllers.vendor.error_repeat', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
            }
            $localFile = \PHPExcel_IOFactory::identify($path);
            $objReader = \PHPExcel_IOFactory::createReader($localFile);
            //Память для Кэширования
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = ['memoryCacheSize ' => '64MB'];
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            //Оптимизируем чтение файла
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($path);
            $worksheet = $objPHPExcel->getSheet(0);

            unset($objPHPExcel);
            unset($objReader);

            $highestRow = $worksheet->getHighestRow(); // получаем количество строк
            $xlsArray = [];

            if ($highestRow > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Ошибка загрузки каталога<br>')
                    . Yii::t('app', '<small>Вы пытаетесь загрузить каталог объемом больше ') . CatalogBaseGoods::MAX_INSERT_FROM_XLS . Yii::t('app', ' позиций, обратитесь к нам и мы вам поможем')
                    . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                unlink($path);
                return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
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
                return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
            }
            unset($xlsArray);

            if ($importType == 1) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $data_insert = [];
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_article = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //артикул
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(1, $row))); //наименование
                        $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                        $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                        $row_ed = strip_tags(trim($worksheet->getCellByColumnAndRow(4, $row))); //единица измерения
                        $row_note = strip_tags(trim($worksheet->getCellByColumnAndRow(5, $row)));  //Комментарий
                        if (!empty($row_product && $row_price && $row_ed)) {
                            if (empty($row_units) || $row_units < 0) {
                                $row_units = 0;
                            }
                            if (!in_array(mb_strtolower($row_product), $arr)) {
                                $new_item = new CatalogBaseGoods;
                                $new_item->cat_id = $id;
                                $new_item->supp_org_id = $vendor_id;
                                $new_item->article = $row_article;
                                $new_item->product = $row_product;
                                $new_item->units = $row_units;
                                $new_item->price = $row_price;
                                $new_item->ed = $row_ed;
                                $new_item->note = $row_note;
                                $new_item->status = CatalogBaseGoods::STATUS_ON;
                                $new_item->save();
                            }
                        }
                    }
                    unset($worksheet);
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(Url::to(['goods/vendor', 'id' => $vendor_id]));
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error', ['ru' => 'Ошибка сохранения, повторите действие'])
                        . Yii::t('error', 'frontend.controllers.vendor.saving_error_two', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                    return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
                }
            }
            if ($importType == 2) {
                $data_update = "";
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $cbgTable = CatalogBaseGoods::tableName();
                    $batch = 0;
                    for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                        $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                        $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(1, $row))); //цена
                        if (!empty($row_product && $row_price)) {
                            if (empty($row_units) || $row_units < 0) {
                                $row_units = 0;
                            }
                            $cbg_id = array_search(mb_strtolower($row_product), $arr);
                            if ($cbg_id) {
                                Yii::$app->db->createCommand()->update($cbgTable, ['price' => $row_price], ['cat_id' => $id, 'id' => $cbg_id])->execute();
                            }
                        }
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(Url::to(['goods/vendor', 'id' => $vendor_id]));
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_three', ['ru' => 'Ошибка сохранения, повторите действие'])
                        . Yii::t('error', 'frontend.controllers.vendor.saving_error_four', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                    return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
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
                                Yii::$app->db->createCommand()->update($cbgTable,
                                    [
                                        'market_place'  => 1,
                                        'mp_show_price' => 1,
                                        'es_status'     => 1
                                    ],
                                    [
                                        'cat_id' => $id,
                                        'id'     => $cbg_id,
                                        ['is not', 'ed', null],
                                        ['is not', 'category_id', null]
                                    ])->execute();
                            }
                        }
                    }
                    $transaction->commit();
                    unlink($path);
                    return $this->redirect(Url::to(['goods/vendor', 'id' => $vendor_id]));
                } catch (Exception $e) {
                    unlink($path);
                    $transaction->rollback();
                    Yii::$app->session->setFlash('success', Yii::t('error', 'frontend.controllers.vendor.saving_error_five', ['ru' => 'Ошибка сохранения, повторите действие'])
                        . Yii::t('error', 'frontend.controllers.vendor.repeat_error', ['ru' => '<small>Если ошибка повторяется, пожалуйста, сообщите нам'])
                        . '<a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
                    return $this->redirect(Url::to(\Yii::$app->request->getReferrer()));
                }
            }
        }
        $id = $vendor_id;
        return $this->renderAjax('_importForm', compact('importModel', 'id'));
    }

    public function actionImportCatalog($id)
    {
        $relation = RelationSuppRest::findOne(['id' => $id]);

        if (empty($relation)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('fail', Yii::t('error', 'backend.controllers.goods.file_error', ['ru' => 'Ошибка загрузки файла!']));
                return $this->render("import-catalog", compact("relation", "importModel"));
            }
            $localFile = \PHPExcel_IOFactory::identify($path);
            $objReader = \PHPExcel_IOFactory::createReader($localFile);
            $objPHPExcel = $objReader->load($path);

            $worksheet = $objPHPExcel->getSheet(0);
            $highestRow = $worksheet->getHighestRow(); // получаем количество строк

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $baseCatalog = Catalog::findOne(['supp_org_id' => $relation->supp_org_id, 'type' => Catalog::BASE_CATALOG]);

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
                        $baseProduct = new CatalogBaseGoods();
                        $baseProduct->cat_id = $baseCatalog->id;
                        $baseProduct->supp_org_id = $relation->supp_org_id;
                        $baseProduct->article = $row_article;
                        $baseProduct->product = $row_product;
                        $baseProduct->units = $row_units;
                        $baseProduct->price = $row_price;
                        $baseProduct->ed = $row_ed;
                        $baseProduct->note = $row_note;
                        $baseProduct->status = CatalogBaseGoods::STATUS_ON;
                        $baseProduct->save();
                        $product = new CatalogGoods();
                        $product->cat_id = $relation->cat_id;
                        $product->base_goods_id = $baseProduct->id;
                        $product->price = $row_price;
                        $product->save();
                    }
                }
                $relation->uploaded_processed = RelationSuppRest::UPLOADED_PROCESSED;
                $relation->save();
                $transaction->commit();
                unlink($path);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
                Yii::$app->session->setFlash('fail', Yii::t('error', 'backend.controllers.goods.save_error', ['ru' => 'Ошибка сохранения, повторите действие!']));
            }
        }
        return $this->render("import-catalog", compact("relation", "importModel"));
    }

    private function fillNewBaseCatalog($worksheet, $highestRow, $catalogId, $vendorId)
    {
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
                $product = new CatalogBaseGoods();
                $product->cat_id = $catalogId;
                $product->supp_org_id = $vendorId;
                $product->article = $row_article;
                $product->product = $row_product;
                $product->units = $row_units;
                $product->price = $row_price;
                $product->ed = $row_ed;
                $product->note = $row_note;
                $product->status = CatalogBaseGoods::STATUS_ON;
                $product->save();
            }
        }
    }

    public function actionView($id)
    {
        $model = CatalogBaseGoods::findOne($id);
        return $this->render('view', [
            'model' => $model
        ]);
    }

    /**
     * Finds the CatalogBaseGoods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return CatalogBaseGoods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CatalogBaseGoods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.goods.page_error', ['ru' => 'The requested page does not exist.']));
        }
    }

}
