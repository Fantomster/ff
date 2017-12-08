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
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $params = new \api\modules\v1\modules\mobile\resources\FavoriteSearch();

        $cbgTable = CatalogBaseGoods::tableName();
        $orderTable = Order::tableName();
        $ordContentTable = OrderContent::tableName();
        $goodsNotesTable = GoodsNotes::tableName();
        $organizationTable = Organization::tableName();
        $currency = \common\models\Currency::tableName();

        $query = CatalogBaseGoods::find();
        $query->select("$cbgTable.*, $organizationTable.name as organization_name, $goodsNotesTable.note as comment, $currency.symbol as symbol");
        $query->leftJoin($catalog,"$catalog.id in (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=catalog_base_goods.supp_org_id) AND (rest_org_id = $client->id))");
        $query->leftJoin($currency,"$currency.id = $catalog.currency_id");
        $query->leftJoin($ordContentTable, "$cbgTable.id=$ordContentTable.product_id");
        $query->leftJoin($orderTable, "$ordContentTable.order_id=$orderTable.id");
        $query->leftJoin($organizationTable, "$organizationTable.id = $cbgTable.supp_org_id");
        $query->leftJoin($goodsNotesTable, "$goodsNotesTable.catalog_base_goods_id = $cbgTable.id and $goodsNotesTable.rest_org_id = $organizationTable.id");

        // add conditions that should always apply here
        //where ord.client_id = 1 and cbg.status=1 and cbg.deleted = 0
        $query->where(["$orderTable.client_id" => $client->id, "$cbgTable.status" => CatalogBaseGoods::STATUS_ON, "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF]);
        $query->groupBy(["$cbgTable.id"]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
             'pagination' => false,
        ]);

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if (isset($params->count)) {
            $query->limit($params->count);
            if (isset($params->page)) {
                $offset = ($params->page * $params->count) - $params->count;
                $query->offset($offset);
            }
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', "$cbgTable.product", $params->searchString]);

        return $dataProvider;
    }
}
