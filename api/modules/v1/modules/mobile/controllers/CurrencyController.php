<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\Currency;
use common\models\RelationSuppRest;
use yii\helpers\Json;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CurrencyController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Currency';

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
        $model = Currency::findOne($id);
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
        $params = new Currency();
        $query = Currency::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));

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
            $query->andWhere ('id IN('.implode(',', Json::decode($params->list)).')');
        
        $query->andFilterWhere([
            'id' => $params->id, 
            'text' => $params->text,
            'symbol' => $params->symbol, 
           ]);
  
        return $dataProvider;
    }
}
