<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
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
        ));

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }

        if($params->parent == 0)
        {
            $query->andWhere (['parent' => null]);
            $query->andFilterWhere([
                'id' => $params->id, 
                'name1' => $params->name, 
               ]);
        }
        else 
             $query->andFilterWhere([
            'id' => $params->id, 
            'parent' => $params->parent, 
            'name2' => $params->name, 
           ]);
        return $dataProvider;
    }
}
