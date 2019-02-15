<?php

namespace backend\controllers;

use backend\models\AmoSearch;
use common\models\AmoFields;
use Yii;
use common\models\Role;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * GoodsController implements the CRUD actions for CatalogBaseGoods model.
 */
class AmoController extends Controller
{

    /**
     * @inheritdoc
     */
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
                        'actions' => ['index', 'update', 'create'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CatalogBaseGoods models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AmoSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Franchisee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AmoFields();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/amo/index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = AmoFields::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru' => 'The requested page does not exist.']));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/amo/index']);
        } else {
            return $this->render('/amo/update', [
                'model' => $model,
            ]);
        }
    }

}
