<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use api\modules\v1\modules\mobile\resources\GoodsNotes;
use yii\data\ActiveDataProvider;
use common\models\RelationSuppRest;
use yii\helpers\Json;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GoodsNotesController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\GoodsNotes';

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
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\GoodsNotes',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => 'common\models\GoodsNotes',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
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
        $model = GoodsNotes::findOne($id);
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
        $params = new GoodsNotes();
        $user = Yii::$app->user->getIdentity();
        
        $query = GoodsNotes::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query->andWhere (['rest_org_id'=>$user->organization_id]);
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
        
        if($params->list != null)
            $query->andWhere ('catalog_base_goods_id IN('.implode(',', Json::decode($params->list)).')');
  
        $query->andFilterWhere([
            'id' => $params->id, 
            'rest_org_id' => $params->rest_org_id, 
            'note' => $params->note,
            'created_at' => $params->created_at, 
            'updated_at' => $params->updated_at, 
            'catalog_base_goods_id' => $params->catalog_base_goods_id
           ]);
        return $dataProvider;
    }
    
   public function checkAccess($action, $model = null, $params = [])
   {
       // check if the user can access $action and $model
       // throw ForbiddenHttpException if access should be denied
       if ($action === 'update' || $action === 'delete') {
           $user = Yii::$app->user->identity;
           if ($model->rest_org_id !== $user->organization_id)
               throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
       }
   }

    
}
