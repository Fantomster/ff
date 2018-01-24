<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\guides\Guide;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\GuideProduct;
use yii\helpers\Json;


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
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => 'common\models\guides\GuideProduct',
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\guides\GuideProduct',
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => 'common\models\guides\GuideProduct',
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
        
        if($params->list != null)
        {
            $query->andWhere ('guide_id IN('.implode(',', Json::decode($params->list)).')');
        }
       
            $filters['id'] = $params->id; 
            $filters['guide_id'] = $params->guide_id; 
            $filters['cbg_id'] = $params->cbg_id; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
  
            $query->andFilterWhere($filters);
  
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
        if ($action === 'create') {
            $model = new \common\models\guides\GuideProduct();
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        }

        if ($action === 'create' || $action === 'update' || $action === 'delete') {
            $user = Yii::$app->user->identity;
            if ($model->guide->client_id !== $user->organization_id)
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s order content that you\'ve created.', $action));
        }
    }

}
