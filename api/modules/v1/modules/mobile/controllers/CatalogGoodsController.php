<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use yii\data\ActiveDataProvider;
use common\models\RelationSuppRest;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogGoodsController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\CatalogGoods';

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
        $model = CatalogGoods::findOne($id);
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
        $params = new CatalogGoods();
        $user = Yii::$app->user->getIdentity();
        
        $query = CatalogGoods::find();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query = CatalogGoods::find()->where(['in','cat_id', RelationSuppRest::find()->select('cat_id')->where(['rest_org_id' => $user->organization_id])]);
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
             $query = CatalogGoods::find()->where(['in','cat_id', RelationSuppRest::find()->select('cat_id')->where(['supp_org_id' => $user->organization_id])]);
     
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
        $query->andFilterWhere([
            'id' => $params->id, 
            'base_goods_id' => $params->base_goods_id, 
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at, 
            'discount_percent' => $params->discount_percent, 
            'discount' => $params->discount, 
            'discount_fixed' => $params->discount_fixed, 
            'price' => $params->price 
           ]);
        return $dataProvider;
    }

    
}
