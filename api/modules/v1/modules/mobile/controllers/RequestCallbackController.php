<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\RequestCallback;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\Request;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestCallbackController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\RequestCallback';

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
        $model = RequestCallback::findOne($id);
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
        $params = new RequestCallback();
        $query = RequestCallback::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        $user = Yii::$app->user->getIdentity();
        
        
                
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
                $query = RequestCallback::find()->where(['in','request_id', Request::find()->select('id')->where(['rest_org_id' => $user->organization_id])]);

            if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
                $query->andWhere (['supp_org_id' => $user->organization_id]);
            
            $dataProvider =  new ActiveDataProvider(array(
                'query' => $query,
                ));
            return $dataProvider;
        }
  
         $query->andFilterWhere([
            'id' => $params->id, 
            'request_id' => $params->request_id, 
            'supp_org_id' => $params->supp_org_id, 
            'price' => $params->price, 
            'comment' => $params->comment, 
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at
           ]);
        return $dataProvider;
    }
}
