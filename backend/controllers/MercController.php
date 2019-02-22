<?php

namespace backend\controllers;

use api\common\models\merc\mercService;
use api\common\models\merc\search\mercServiceSearch;
use yii\web\NotFoundHttpException;
use common\models\Role;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\web\Controller;

class MercController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['index', 'update', 'create', 'delete', 'autocomplete'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new mercServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Organization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreate()
    {
        $model = new mercService();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            return $this->redirect(['index']);
        }
    }

    public function actionAutocomplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!is_null($term)) {
            $query = new \yii\db\Query;
            $query->select(['id' => 'o.id', 'text' => 'CONCAT("(ID:",o.id,") ",o.name)'])
                ->from('organization o')
                ->where('o.type_id in (1,2)')
                ->andwhere("o.id like :id or o.name like :name", [':id' => '%' . $term . '%', ':name' => '%' . $term . '%'])
                ->limit(20);

            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
    }

    protected function findModel($id)
    {
        if (($model = mercService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}