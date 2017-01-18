<?php

namespace backend\controllers;

use Yii;
use common\models\CatalogBaseGoods;
use backend\models\CatalogBaseGoodsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;

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

        return $this->render('vendor', compact('id', 'searchModel', 'dataProvider'));
    }
    
    public function actionAjaxUpdateProductMarketPlace($id) {
        $catalogBaseGoods = CatalogBaseGoods::find()->where(['id' => $id])->one();
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

                //var_dump($catalogBaseGoods);
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
        return $this->renderAjax('_form', compact('catalogBaseGoods', 'countrys'));
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
        $dataProviderEmpty->query->andWhere(['category_id' => null, 'supp_org_id' => $vendor_id]);
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
