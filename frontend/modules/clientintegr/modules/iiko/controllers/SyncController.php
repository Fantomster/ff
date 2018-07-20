<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoCategory;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use api_web\modules\integration\modules\iiko\models\iikoSync as WebApiIikoSync;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Response;

class SyncController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    public $organisation_id;
    public $ajaxActions = ['run'];

    public function beforeAction($action)
    {
        $user = User::findOne(\Yii::$app->user->id);
        $this->organisation_id = $user->organization_id;

        if (empty($this->organisation_id)) {
            return false;
        }

        if (in_array($this->action->id, $this->ajaxActions)) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            set_time_limit(3600);
        }

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => $this->ajaxActions,
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['POST'],
                        'matchCallback' => function () {
                            return \Yii::$app->request->isAjax;
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionGoodsView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoProduct::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('goods-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionCategoryView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoCategory::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('category-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionStoreView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoStore::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('store-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionAgentView()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => iikoAgent::find()->where(['org_id' => $this->organisation_id])
        ]);

        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Синхронизация всего, по типам
     * @return array
     */
    public function actionRun()
    {
        $id = \Yii::$app->request->post('id');
        try {
            return (new WebApiIikoSync())->run($id);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()];
        }
    }
}
