<?php

namespace backend\controllers;

use Yii;
use common\models\CatalogBaseGoods;
use common\models\Role;
use common\models\RelationSuppRest;
use common\models\Catalog;
use common\models\CatalogGoods;
use backend\models\CatalogBaseGoodsSearch;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

/**
 * GoodsController implements the CRUD actions for CatalogBaseGoods model.
 */
class GoodsController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['ajax-clear-category', 'ajax-set-category', 'ajax-update-product-market-place', 'import-catalog'],
                        'allow' => true,
                        'roles' => [Role::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['index', 'vendor', 'view', 'category', 'get-sub-cat', 'mp-country', 'uploaded-catalogs'],
                        'allow' => true,
                        'roles' => [
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
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new CatalogBaseGoodsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionVendor($id) {
        $searchModel = new CatalogBaseGoodsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $dataProvider->query->andWhere(['supp_org_id' => $id]);
        $isEditable = true;
        return $this->render('vendor', compact('id', 'searchModel', 'dataProvider', 'isEditable'));
    }


    public function actionAjaxUpdateProductMarketPlace($id, $supp_org_id = null) {
        if($id){
            $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
        }else{
            $catalogBaseGoods = new CatalogBaseGoods();
        }
        $catalogBaseGoods->scenario = 'marketPlace';
        $sql = "SELECT id, name FROM mp_country WHERE name = \"Россия\"
	UNION SELECT id, name FROM mp_country WHERE name <> \"Россия\"";
        $countrys = \Yii::$app->db->createCommand($sql)->queryAll();
//        $categorys = new \yii\base\DynamicModel([
//            'sub1', 'sub2'
//        ]);
//        $categorys->addRule(['sub1', 'sub2'], 'required', ['message' => Yii::t('app', 'Укажите категорию товара')])
//                ->addRule(['sub1', 'sub2'], 'integer');
//        if (!empty($catalogBaseGoods->category_id)) {
//            $categorys->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
//            $categorys->sub2 = $catalogBaseGoods->category_id;
//        }
        if (!empty($catalogBaseGoods->category_id)) {
            $catalogBaseGoods->sub1 = \common\models\MpCategory::find()->select(['parent'])->where(['id' => $catalogBaseGoods->category_id])->one()->parent;
            $catalogBaseGoods->sub2 = $catalogBaseGoods->category_id;
        }

        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($catalogBaseGoods->load($post)) {
                $catalogBaseGoods->price = preg_replace("/[^-0-9\.]/", "", str_replace(',', '.', $catalogBaseGoods->price));
                if($supp_org_id){
                    $catalogBaseGoods->supp_org_id = $supp_org_id;
                    $catalogBaseGoods->cat_id = $supp_org_id;
                }
                if ($catalogBaseGoods->market_place == 1) {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub2;
                        $catalogBaseGoods->es_status = 1;
                        $catalogBaseGoods->save();
                        $message = 'Продукт обновлен!';
                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                } else {
                    if ($post && $catalogBaseGoods->validate()) {
                        $catalogBaseGoods->category_id = $catalogBaseGoods->sub1 ? $catalogBaseGoods->sub2 : null;
                        $catalogBaseGoods->es_status = 2;
                        $catalogBaseGoods->save();
                        $message = 'Продукт обновлен!';
                        return $this->renderAjax('_success', ['message' => $message]);
                    }
                }
            }
        }
        return $this->renderAjax('_form', compact('catalogBaseGoods', 'countrys', 'supp_org_id'));
    }

    public function actionMpCountryList($q) {
        if (Yii::$app->request->isAjax) {
            $model = new \common\models\MpCountry();
            Yii::$app->response->format = Response::FORMAT_JSON;
            //return 'aaa';
            return $model->ajaxsearch($q);
        }
        return false;
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
                echo Json::encode(['output' => $out, 'selected' => $selected]);
                return;
            }
        }
        echo Json::encode(['output' => '', 'selected' => '']);
    }

    public function actionCategory($vendor_id, $id) {
        $vendor = \common\models\Organization::findOne(['id' => $vendor_id]);

        $searchModel = new CatalogBaseGoodsSearch();

        $dataProviderCategory = $searchModel->search();
        $dataProviderCategory->query->andWhere(['category_id' => $id, 'supp_org_id' => $vendor_id]);

        $dataProviderEmpty = $searchModel->search();
        $dataProviderEmpty->query->andWhere(['supp_org_id' => $vendor_id]);
        $dataProviderEmpty->query->andWhere('(category_id is null) OR (category_id = 0)');
        $subCategory = \common\models\MpCategory::findOne(['id' => $id]);
        $category = \common\models\MpCategory::findOne(['id' => $subCategory->parent]);

        return $this->render('category', compact('id', 'dataProviderCategory', 'dataProviderEmpty', 'vendor', 'subCategory', 'category'));
    }

    public function actionAjaxClearCategory() {
        $post = Yii::$app->request->post();
        if ($post) {
            $product = CatalogBaseGoods::findOne(['id' => $post['id']]);
            $product->category_id = null;
            return $product->save(false);
        }
        return false;
    }

    public function actionAjaxSetCategory() {
        $post = Yii::$app->request->post();
        if ($post) {
            $product = CatalogBaseGoods::findOne(['id' => $post['id']]);
            $product->category_id = $post['category_id'];
            return $product->save(false);
        }
        return false;
    }

    public function actionUploadedCatalogs() {
        $query = RelationSuppRest::find()->where('uploaded_catalog is not null')->andWhere(['uploaded_processed' => RelationSuppRest::UPLOADED_NOT_PROCESSED]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->render("uploaded-catalogs", compact("dataProvider"));
    }

    public function actionImportCatalog($id) {
        $relation = RelationSuppRest::findOne(['id' => $id]);
        if (empty($relation)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $importModel = new \common\models\upload\UploadForm();
        if (Yii::$app->request->isPost) {
            $importModel->importFile = UploadedFile::getInstance($importModel, 'importFile'); //загрузка файла на сервер
            $path = $importModel->upload();
            if (!is_readable($path)) {
                Yii::$app->session->setFlash('fail', 'Ошибка загрузки файла!');
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
                Yii::$app->session->setFlash('fail', 'Ошибка сохранения, повторите действие!');
            }
        }
        return $this->render("import-catalog", compact("relation", "importModel"));
    }

    private function fillNewBaseCatalog($worksheet, $highestRow, $catalogId, $vendorId) {
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
     * @param integer $id
     * @return CatalogBaseGoods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = CatalogBaseGoods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
