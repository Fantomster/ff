<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\Delivery;
use common\models\RelationSuppRest;
use yii\helpers\Json;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class DeliveryController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Delivery';

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
        $model = Delivery::findOne($id);
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
        $params = new Delivery();
        $query = Delivery::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        $user = Yii::$app->user->getIdentity();
        
         if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
            $query->andWhere (['in ','vendor_id', RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $user->organization_id])]);

        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
            $query->andWhere('vendor_id = '.$user->organization_id);

        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
        
        if(isset($params->count))
        {
            $query->limit($params->count);
                if(isset($params->page))
                {
                    $offset = ($params->page * $params->count) - $params->count;
                    $query->offset($offset);
                }
        }
  
       
        if($params->list != null)
            $query->andWhere ('vendor_id IN('.implode(',', Json::decode($params->list)).')');
        
        $query->andFilterWhere([
            'id' => $params->id, 
            'vendor_id' => $params->vendor_id,
            'delivery_charge' => $params->delivery_charge, 
            'min_free_delivery_charge' => $params->min_free_delivery_charge, 
            'mon' => $params->mon, 
            'tue' => $params->tue, 
            'wed' => $params->wed, 
            'thu' => $params->thu, 
            'fri' => $params->fri, 
            'sat' => $params->sat, 
            'sun' => $params->sun, 
            'min_order_price' => $params->min_order_price, 
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at, 
            
           ]);
  
        return $dataProvider;
    }
}
