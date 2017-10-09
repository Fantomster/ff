<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Organization;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\models\User;
use common\models\forms\LoginForm;
use common\models\RelationSuppRest;
use yii\helpers\Json;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrganizationController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Organization';

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
        $model = Organization::findOne($id);
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
        $params = new Organization();
        $user = Yii::$app->user->getIdentity();
        
        $query = Organization::find();

        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        
        $filters = [];
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
            $query->andWhere (['in','id', RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $user->organization_id])]);

        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
            $query->andWhere(['in','id', RelationSuppRest::find()->select('rest_org_id')->where(['supp_org_id' => $user->organization_id])]);


        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
          
            $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            ));
            
            return $dataProvider;
        }
        
            if($params->list != null)
            $query->andWhere ('id IN('.implode(',', Json::decode($params->list)).')');

             
            $filters['id'] = $params->id; 
            $filters['name'] = $params->name; 
            $filters['type_id'] = $params->type_id; 
            
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }

    
}
