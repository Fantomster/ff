<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use common\models\MpCategory;
use yii\helpers\Json;
use yii\data\SqlDataProvider;
use yii\data\Pagination;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogBaseGoodsController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\CatalogBaseGoods';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = CatalogBaseGoods::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
    
    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = new CatalogBaseGoods();
        $params->load(Yii::$app->request->queryParams);

        $fieldsCBG = "cbg.id as id, cbg.product, cbg.units, cbg.price, cbg.cat_id, cbg.weight, org.name as organization_name, cbg.ed, curr.symbol, cbg.note, cbg.supp_org_id as supp_org_id, cbg.created_at as created_at ";
        $fieldsCG = "cbg.id as id, cbg.product, cbg.units, coalesce( cg.price, cbg.price) as price, cbg.cat_id, cbg.weight, org.name as organization_name, cbg.ed, curr.symbol, cbg.note, cbg.supp_org_id as supp_org_id, cbg.created_at as created_at ";

        $where = '';
        $where_all = '';
        $params_sql = [];

        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $selectedVendor = null;
        $selectedCategory = null;

        if(!empty($params->product)) {
            $where .= 'AND (cbg.product LIKE :searchString OR cbg.article LIKE :searchString)';
            $params_sql[':searchString'] = "%" . $params->product . "%";
        }

        if(!empty($params->vendor_id)) {
            $where .= ' AND org.id IN (' . $params->vendor_id . ') ';
            $selectedVendor = $params->vendor_id;
        }

        if(!empty($params->category_id)) {
            $categories = \api\modules\v1\modules\mobile\resources\MpCategory::getCategories($params->category_id);
            $categories[] = $params->category_id;
            $categories = implode(",", $categories);
            $where .= ' AND category_id IN (' .$categories. ') ';
        }

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int) $params['OrderCatalogSearch']['selectedVendor'] : null;
        }

        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $sql = "
        SELECT * FROM (
           SELECT 
              $fieldsCBG
           FROM catalog_base_goods cbg
             LEFT JOIN organization org ON cbg.supp_org_id = org.id
             LEFT JOIN catalog cat ON cbg.cat_id = cat.id
             LEFT JOIN currency curr ON cat.currency_id = curr.id
           WHERE
           cat_id IN (" . $catalogs . ")
           ".$where."
           AND (cbg.status = 1 AND cbg.deleted = 0)
        UNION ALL
          SELECT 
          $fieldsCG
          FROM catalog_goods cg
           LEFT JOIN catalog_base_goods cbg ON cg.base_goods_id = cbg.id
           LEFT JOIN organization org ON cbg.supp_org_id = org.id
           LEFT JOIN catalog cat ON cg.cat_id = cat.id
           LEFT JOIN currency curr ON cat.currency_id = curr.id
          WHERE 
          cg.cat_id IN (" . $catalogs . ")
          ".$where."
          AND (cbg.status = 1 AND cbg.deleted = 0)
        ) as c WHERE id != 0 ".$where_all;

        $query = Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->sql,
            'params' => $params_sql,
            'pagination' => [
                'page' => isset($params->page) ? ($params->page-1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
            'sort' => [
                'attributes' => [
                    'product',
                    'price',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        return $dataProvider;
    }

    private function getCategories($cat_id) {
        $res[] = $cat_id;
        $cats = MpCategory::find()->where(["parent" => $cat_id])->all();
        foreach ($cats as $cat) {
            $res[] = $cat->id;
            $res = array_merge($res, $this->getCategories($cat->id));
        }

        return $res;
    }
}
