<?php

namespace backend\controllers;

use Yii;
use common\models\WhiteList;
use common\models\Organization;
use common\models\Role;
use backend\models\WhiteListSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * WhiteListController implements the CRUD actions for WhiteList model.
 */
class WhiteListController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['update', 'delete', 'approve'],
                        'allow' => true,
                        'roles' => [Role::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all WhiteList models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WhiteListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WhiteList model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WhiteList model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new WhiteList();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }

    /**
     * Updates an existing WhiteList model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing WhiteList model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionApprove($id) {
        if (($wl = WhiteList::findOne($id)) !== null) {
            return $this->redirect(['organization/index']);
        } elseif (($org = Organization::findOne($id)) !== null) {
            $new = new WhiteList();
            $new->organization_id = $org->id;
            $new->legal_entity = $org->legal_entity;
            $new->legal_email = $org->email;
            $new->phone = $org->phone;
            $new->save();
            return $this->redirect(['white-list/update', 'id' => $new->id]);
        }
        return $this->redirect(['organization/index']);
    }

    /**
     * Finds the WhiteList model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WhiteList the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WhiteList::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
