<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\Order;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\helpers\Json;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class FavoritesController extends ActiveController {

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
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
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
     * @return SqlDataProvider
     */
    public function prepareDataProvider()
    {
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $params = new \api\modules\v1\modules\mobile\resources\FavoriteSearch();

        $query = "
            SELECT
                cbg.id as id, cbg.product, cbg.units, cbg.price, cbg.cat_id, cbg.article, cbg.supp_org_id, cbg.category_id, org.name as organization_name, cbg.ed, curr.symbol, cbg.note
            FROM order_content AS oc
                LEFT JOIN " . Order::tableName() . " AS ord ON oc.order_id = ord.id
                LEFT JOIN catalog_base_goods AS cbg ON oc.product_id = cbg.id
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                    AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            GROUP BY cbg.id
            UNION ALL
            (SELECT
                cbg.id as id, cbg.product, cbg.units, cg.price, cg.cat_id, cbg.article, cbg.supp_org_id, cbg.category_id, org.name as organization_name, cbg.ed, curr.symbol, cbg.note
            FROM order_content AS oc
                LEFT JOIN " . Order::tableName() . " AS ord ON oc.order_id = ord.id
                LEFT JOIN catalog_base_goods AS cbg ON oc.product_id = cbg.id
                LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = oc.product_id 
                    AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cg.cat_id = cat.id
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
               (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            GROUP BY cbg.id)
        ";

        $query1 = "
            SELECT
                COUNT(DISTINCT cbg.id) 
            FROM order_content AS oc
                LEFT JOIN " . Order::tableName() . " AS ord ON oc.order_id = ord.id
                LEFT JOIN catalog_base_goods AS cbg ON oc.product_id = cbg.id
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                    AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
                (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";

        $count1 = Yii::$app->db->createCommand($query1)->queryScalar();

        $query2 = "
            SELECT
                COUNT(DISTINCT cbg.id) 
            FROM order_content AS oc
                LEFT JOIN " . Order::tableName() . " AS ord ON oc.order_id = ord.id
                LEFT JOIN catalog_base_goods AS cbg ON oc.product_id = cbg.id
                LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = oc.product_id 
                    AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cg.cat_id = cat.id
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE 
               (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";

        $count2 = Yii::$app->db->createCommand($query2)->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql'        => $query,
            'totalCount' => 20,
            'sort'       => [
                'attributes'   => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $dataProvider->pagination = false;
            return $dataProvider;
        }

        if (isset($params->count)) {
            $dataProvider->pagination->pageSize = $params->count;
            if (isset($params->page)) {
                $dataProvider->pagination->page = ($params->page - 1);
            }
        }

        return $dataProvider;
    }
}
