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
        $params = new \common\models\search\FavoriteSearch();

        $cbgTable = CatalogBaseGoods::tableName();
        $orderTable = Order::tableName();
        $ordContentTable = OrderContent::tableName();
        $goodsNotesTable = GoodsNotes::tableName();
        $organizationTable = Organization::tableName();

        $query = CatalogBaseGoods::find();
        $query->select("$cbgTable.*, $organizationTable.name as organization_name, $goodsNotesTable.note as comment");
        $query->leftJoin($ordContentTable, "$cbgTable.id=$ordContentTable.product_id");
        $query->leftJoin($orderTable, "$ordContentTable.order_id=$orderTable.id");
        $query->leftJoin($organizationTable, "$organizationTable.id = $cbgTable.supp_org_id");
        $query->leftJoin($goodsNotesTable, "$goodsNotesTable.catalog_base_goods_id = $cbgTable.id");

        // add conditions that should always apply here
        //where ord.client_id = 1 and cbg.status=1 and cbg.deleted = 0
        $query->where(["$orderTable.client_id" => $client->id, "$cbgTable.status" => CatalogBaseGoods::STATUS_ON, "$cbgTable.deleted" => CatalogBaseGoods::DELETED_OFF]);
        $query->groupBy(["$cbgTable.id"]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
          //  'sort' => ['defaultOrder' => ["$orderTable.created_at" => SORT_DESC]],
        ]);

        $dataProvider->pagination = ['pageSize' => 15];
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', "$cbgTable.product", $params->searchString]);

        return $dataProvider;
    }
}
