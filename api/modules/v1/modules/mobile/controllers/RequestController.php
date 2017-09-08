<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Request;
use yii\data\ActiveDataProvider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RequestController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Request';

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
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\Request',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => 'common\models\Request',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
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
        $model = Request::findOne($id);
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
        $params = new Request();
        $query = Request::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        
        $user = Yii::$app->user->getIdentity();
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query->andWhere (['rest_org_id'=>$user->organization_id]);
       
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
  
         $query->andFilterWhere([
            'id' => $params->id, 
            'category' => $params->category, 
            'product' => $params->product, 
            'comment' => $params->comment, 
            'regular' => $params->regular, 
            'amount' => $params->amount, 
            'rush_order' => $params->rush_order, 
            'payment_method' => $params->payment_method, 
            'deferment_payment' => $params->deferment_payment,
            'responsible_supp_org_id' => $params->responsible_supp_org_id, 
            'count_views' => $params->count_views, 
            'created_at' => $params->created_at, 
            'end' => $params->end, 
            'rest_org_id' => $params->rest_org_id, 
            'active_status' => $params->active_status
           ]);
        return $dataProvider;
    }
    
    /**
    * Checks the privilege of the current user.
    *
    * This method should be overridden to check whether the current user has the privilege
    * to run the specified action against the specified data model.
    * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
    *
    * @param string $action the ID of the action to be executed
    * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
    * @param array $params additional parameters
    * @throws ForbiddenHttpException if the user does not have access
    */
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
