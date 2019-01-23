<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Order;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\resources\Guide;
use yii\web\ServerErrorHttpException;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Guide';

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
                'modelClass' => 'common\models\guides\Guide',
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'common\models\guides\Guide',
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
        $model = Guide::findOne($id);
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
        $params = new Guide();
        $query = Guide::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();
        
        $filters['client_id'] = $user->organization_id;

        $query->select([
            'guide.*',
            '(select catalog_base_goods.product from guide_product left join catalog_base_goods on catalog_base_goods.id = guide_product.cbg_id where guide_product.guide_id = guide.id and catalog_base_goods.status = 1 and catalog_base_goods.deleted = 0 limit 1 ) as product1',
            '(select catalog_base_goods.product from guide_product left join catalog_base_goods on catalog_base_goods.id = guide_product.cbg_id where guide_product.guide_id = guide.id and catalog_base_goods.status = 1 and catalog_base_goods.deleted = 0 limit 1 offset 1) as product2',
            '(select catalog_base_goods.product from guide_product left join catalog_base_goods on catalog_base_goods.id = guide_product.cbg_id where guide_product.guide_id = guide.id and catalog_base_goods.status = 1 and catalog_base_goods.deleted = 0 limit 1 offset 2) as product3']);
        $query->leftJoin('(select count(id) as count, guide_id, cbg_id from guide_product group by guide_id) as gp', 'gp.guide_id = guide.id');
        $query->andWhere('gp.count is not null');

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
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
  
       
            $filters['id'] = $params->id; 
            $filters['type'] = $params->type; 
            $filters['name'] = $params->name; 
            $filters['deleted'] = $params->deleted; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
  
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }

    public function actionDelete ($id)
    {
        $guide = Guide::findOne(['id' => $id]);
        $this->checkAccess('delete',$guide);
        $guide->delete();
        Yii::$app->getResponse()->setStatusCode(204);
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
    public function checkAccess($action, $model = null, $params = []) {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
            $user = Yii::$app->user->identity;

            if ($model->client_id !== $user->organization_id)
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }
}
