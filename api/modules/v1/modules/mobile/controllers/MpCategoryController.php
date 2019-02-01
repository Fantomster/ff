<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\db\Expression;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\MpCategory;
use yii\data\ActiveDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class MpCategoryController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\MpCategory';

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
            /*'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],*/
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = MpCategory::findOne($id);
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
        $params = new MpCategory();
        $query = MpCategory::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andWhere (['parent' => null]);
            return $dataProvider;
        }

        if ($params->language != null) {
            $query->select([
                'mp_category.id',
                'mp_category.parent',
                'mp_category.slug',
                'mp_category.title',
                'mp_category.text',
                'mp_category.description',
                'mp_category.keywords',
                new Expression('IFNULL(message.translation, mp_category.name) as name')]);
            $query->innerJoin('source_message', "source_message.category = 'app' and source_message.message = mp_category.name");
            $query->innerJoin('message', 'message.id = source_message.id and message.language = "' . $params->language . '"');
        }

        if($params->parent == 0)
        {
            $query->andWhere (['parent' => null]);
            $query->andFilterWhere([
                'id' => $params->id, 
                'name' => $params->name, 
               ]);
        }
        else 
             $query->andFilterWhere([
            'id' => $params->id, 
            'parent' => $params->parent, 
            'name' => $params->name, 
           ]);
        return $dataProvider;
    }

    public function actionIndex()
    {
        $params = new MpCategory();
        $params->load(Yii::$app->request->queryParams);

        $dataProvider = $this->prepareDataProvider();
        $models = $dataProvider->getModels();
        $res = [];
        foreach ($models as $model) {
            $count = $model->getCountProducts();
            if($params->empty != null || $count > 0)
                $res[] = ['id' => $model->id, 'parent' => $model->parent, 'name' => $model->name, 'count' => $count];
        }

        return $res;
    }
}
