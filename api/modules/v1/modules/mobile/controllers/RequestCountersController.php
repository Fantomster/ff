<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\RequestCounters;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\Request;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestCountersController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\RequestCounters';

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
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = RequestCounters::findOne($id);
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
        $params = new RequestCounters();
        $query = RequestCounters::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        $user = Yii::$app->user->getIdentity();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
            $query->andWhere(['in','request_id', Request::find()->select('id')->where(['rest_org_id' => $user->organization_id])]);
                
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
         $query->andFilterWhere([
            'request_id' => $params->request_id, 
           ]);
        return $dataProvider;
    }
}
