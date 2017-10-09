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
                'findModel' => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess'],
            ],
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
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
                $query->andWhere(['in','request_id', Request::find()->select('id')->where(['rest_org_id' => $user->organization_id])]);

        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
                $query->andWhere (['supp_org_id' => $user->organization_id]);
          
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
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
       if ($action === 'update' || $action === 'delete' || $action === 'view') {
           $user = Yii::$app->user->identity;
           
           if ($model->request->rest_org_id !== $user->organization_id)
               throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
       }
   }
}
