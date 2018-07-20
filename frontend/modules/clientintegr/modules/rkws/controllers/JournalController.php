<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api_web\modules\integration\modules\rkeeper\models\rkeeperService;
use yii\web\Controller;
use common\models\Journal;
use common\models\search\JournalSearch;
use yii\data\Sort;
use yii\web\NotFoundHttpException;

class JournalController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new JournalSearch();
        $searchModel->service_id = rkeeperService::getServiceId();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        $sort = new Sort();
        $sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->setSort($sort);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Journal model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Journal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Journal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Journal::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}