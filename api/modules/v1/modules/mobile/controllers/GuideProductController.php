<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\GuideProduct;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProductController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\GuideProduct';

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
        $model = GuideProduct::findOne($id);
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
        $params = new GuideProduct();
        $query = GuideProduct::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        $filters = [];

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }
       
            $filters['id'] = $params->id; 
            $filters['guide_id'] = $params->guide_id; 
            $filters['cbg_id'] = $params->cbg_id; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
  
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }
}
