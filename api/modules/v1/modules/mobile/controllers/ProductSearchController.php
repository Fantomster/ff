<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use common\models\CatalogGoods;
use common\models\Order;
use common\models\OrderContent;
use common\models\GoodsNotes;
use common\models\Organization;
use yii\helpers\Json;
use common\models\guides\GuideProduct;
use yii\db\Query;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ProductSearchController extends ActiveController {

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
        $params = new \api\modules\v1\modules\mobile\resources\GuideProductSearch();
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        
        $cbgTable = CatalogBaseGoods::tableName();
        $goodsNotesTable = GoodsNotes::tableName();
        $organizationTable = Organization::tableName();
        $currency = \common\models\Currency::tableName();
        $catalog = \common\models\Catalog::tableName();
        
        $symbols_t = 'REPLACE(product, "&quot;", "\'"\) as product';
        
        $symbols_t = 'REPLACE(product, "&quot;", "\'"\) as product';
        
        $query = CatalogBaseGoods::find();
       /* $query->select(["$cbgTable.id", "$cbgTable.cat_id", "$cbgTable.article", "REPLACE(product, '&quot;', '\"'\) as product", 
                "$cbgTable.status", "$cbgTable.market_place", "$cbgTable.deleted", "$cbgTable.created_at", "$cbgTable.updated_at", "$cbgTable.supp_org_id",
                "$cbgTable.price", "$cbgTable.units", "$cbgTable.category_id", "$cbgTable.note", "$cbgTable.ed", "$cbgTable.omage", "$cbgTable.brand",
                "$cbgTable.region", "$cbgTable.weight", "$cbgTable.es_status", "$cbgTable.mp_show_price",
                "$cbgTable.rating", "$organizationTable.name as organization_name", "$goodsNotesTable.note as comment"]);*/
        $query->select("$cbgTable.*, $organizationTable.name as organization_name, $goodsNotesTable.note as comment, $currency.symbol as symbol");

        $query->from("guide_product");
        $query->leftJoin($cbgTable,"$cbgTable.id = guide_product.cbg_id");
        $query->leftJoin($catalog,"$catalog.id in (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=catalog_base_goods.supp_org_id) AND (rest_org_id = $client->id))");
        $query->leftJoin($currency,"$currency.id = $catalog.currency_id");
        $query->leftJoin($organizationTable, "$organizationTable.id = $cbgTable.supp_org_id");
        $query->leftJoin($goodsNotesTable, "$goodsNotesTable.catalog_base_goods_id = $cbgTable.id and $goodsNotesTable.rest_org_id = $organizationTable.id");
        // add conditions that should always apply here
        
        $orderTable = Order::tableName();
        $ordContentTable = OrderContent::tableName();

        $query2 = CatalogBaseGoods::find();
        /*$query2->select("$cbgTable.id", "$cbgTable.cat_id", "$cbgTable.article", "REPLACE(product, '&quot;', '\"'\) as product", 
                "$cbgTable.status", "$cbgTable.market_place", "$cbgTable.deleted", "$cbgTable.created_at", "$cbgTable.updated_at", "$cbgTable.supp_org_id",
                "$cbgTable.price", "$cbgTable.units", "$cbgTable.category_id", "$cbgTable.note", "$cbgTable.ed", "$cbgTable.omage", "$cbgTable.brand",
                "$cbgTable.region", "$cbgTable.weight", "$cbgTable.es_status", "$cbgTable.mp_show_price",
                "$cbgTable.rating", "$organizationTable.name as organization_name", "$goodsNotesTable.note as comment"]);*/

        $query2->select("$cbgTable.*, $organizationTable.name as organization_name, $goodsNotesTable.note as comment, $currency.symbol as symbol");
        $query2->leftJoin($catalog,"$catalog.id in (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=catalog_base_goods.supp_org_id) AND (rest_org_id = $client->id))");
        $query2->leftJoin($currency,"$currency.id = $catalog.currency_id");
        $query2->leftJoin($ordContentTable, "$cbgTable.id=$ordContentTable.product_id");
        $query2->leftJoin($orderTable, "$ordContentTable.order_id=$orderTable.id");
        $query2->leftJoin($organizationTable, "$organizationTable.id = $cbgTable.supp_org_id");
        $query2->leftJoin($goodsNotesTable, "$goodsNotesTable.catalog_base_goods_id = $cbgTable.id and $goodsNotesTable.rest_org_id = $organizationTable.id");

        // add conditions that should always apply here
        //where ord.client_id = 1 and cbg.status=1 and cbg.deleted = 0
        $query2->where(["$orderTable.client_id" => $client->id, "$cbgTable.status" => CatalogBaseGoods::STATUS_ON, "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => false,
        ]);

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
                $query->leftJoin ("guide","guide.id = guide_product.guide_id")->where ("guide.client_id = ".$client->id);
                $query->union($query2);
                
                $query_r = new Query();
                $query_r->select('*')->from(['u' => $query]);//->orderBy(['id' => SORT_DESC]);
                $dataProvider->query = $query_r;
            return $dataProvider;
        }

        $query->leftJoin ("guide","guide.id = guide_product.guide_id")->where ("guide.client_id = ".$client->id);
        
        $query->union($query2);
        $query_r = new Query();
        $query_r->select('*')->from(['u' => $query]);
        $dataProvider->query = $query_r;
        
        if (isset($params->count)) {
            $query_r->limit($params->count);
            if (isset($params->page)) {
                $offset = ($params->page * $params->count) - $params->count;
                $query_r->offset($offset);
            }
        }
       
        // grid filtering conditions
        $query_r->andFilterWhere(['like', 'catalog_base_goods.product', $params->searchString]);

        return $dataProvider;
    }
}
