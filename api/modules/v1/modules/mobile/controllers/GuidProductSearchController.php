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


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuidProductSearchController extends ActiveController {

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
        
        $query = CatalogBaseGoods::find();
        $query->select("$cbgTable.*, $organizationTable.name as organization_name, $goodsNotesTable.note as comment");
        $query->from("guide_product");
        $query->leftJoin($cbgTable,"$cbgTable.id = guide_product.cbg_id");
        $query->leftJoin($organizationTable, "$organizationTable.id = $cbgTable.supp_org_id");
        $query->leftJoin($goodsNotesTable, "$goodsNotesTable.catalog_base_goods_id = $cbgTable.id and $goodsNotesTable.rest_org_id = $organizationTable.id");
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => false,
        ]);

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if($params->guide_id != null)
            $query->where([
                'guide_product.guide_id' => $params->guide_id,
            ]);
        else
            $query->leftJoin ("guide","guide.id = guide_product.guide_id")->where ("guide.client_id = ".$client->id);
        
        if (isset($params->count)) {
            $query->limit($params->count);
            if (isset($params->page)) {
                $offset = ($params->page * $params->count) - $params->count;
                $query->offset($offset);
            }
        }
        
        // grid filtering conditions
        $query->andFilterWhere(['like', 'catalog_base_goods.product', $params->searchString]);

        return $dataProvider;
    }
}
