<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;
use yii\helpers\Json;
use yii\data\SqlDataProvider;


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

        /*$query = CatalogBaseGoods::find();

        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        */

        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;

        $query1 = "
            SELECT  cbg.id as id, cbg.product, cbg.units, cbg.price, cbg.cat_id, cbg.weight, org.name as organization_name, cbg.ed, curr.symbol, cbg.note 
            FROM catalog_base_goods as cbg
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (cbg.status = 1) 
                AND (cbg.deleted = 0) 
                ";

        $query2 = "SELECT cbg.id as id, cbg.product, cbg.units, cg.price, cg.cat_id, cbg.weight, org.name as organization_name, cbg.ed, curr.symbol, cbg.note
            FROM catalog_base_goods AS cbg 
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = cbg.id
                            AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (cbg.status = 1) 
                AND (cbg.deleted = 0)
                ";

        $dataProvider = new SqlDataProvider([
            'sql' => "$query1  UNION ALL ($query2)",
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

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }

        $andWhere = "";
        if($params->list != null)
        {
            $andWhere = 'AND (cbg.id IN('.implode(',', Json::decode($params->list)).')) ';
        }

        if($params->vendor_id != null) {
            $andWhere .= 'AND (cbg.id IN (select base_goods_id from catalog_goods where cat_id in (select cat_id from relation_supp_rest where supp_org_id = '.$params->vendor_id.'))) ';
        }

        if($params->product != null)
        {
            $andWhere = 'AND (product LIKE \'%'.$params->product.'%\') ';
        }

        /*if($params->rest_org_id != null) {
            $andWhere = 'AND id IN ('.implode(',',  CatalogGoods::find()->select('base_goods_id')->where(['in', 'cat_id',
                    RelationSuppRest::find()->select('cat_id')->where(['rest_org_id' => $params->rest_org_id])])
                ).')) ';
        }*/

        if($params->category_id != null)
            $andWhere .= "AND cbg.category_id = $params->category_id";


        $query1 .= $andWhere;
        $query2 .= $andWhere;
        $dataProvider->sql = "$query1  UNION ALL ($query2)";

        /*$query->andFilterWhere([
            'id' => $params->id, 
            'cat_id' => $params->cat_id, 
            'article' => $params->article, 
            'product' => $params->product, 
            'status' => ($params->status == null)?CatalogBaseGoods::STATUS_ON:$params->status, 
            //'market_place' => $params->market_place, 
            'deleted' => $params->deleted, 
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at, 
            'category_id' => $params->category_id, 
            'note' => $params->note, 
            'ed' => $params->ed, 
            'brand' => $params->brand, 
            'region' => $params->region, 
            'weight' => $params->weight, 
            'es_status' => $params->es_status, 
            //'mp_show_price' => $params->mp_show_price, 
            'rating' => $params->rating
            ]);*/
        return $dataProvider;
    }
}
