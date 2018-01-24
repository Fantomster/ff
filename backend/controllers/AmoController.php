<?php

namespace backend\controllers;

use backend\models\AmoSearch;
use backend\models\CurrencySearch;
use common\models\AmoFields;
use common\models\Currency;
use Yii;
use common\models\CatalogBaseGoods;
use common\models\Role;
use common\models\RelationSuppRest;
use common\models\Catalog;
use common\models\CatalogGoods;
use backend\models\CatalogBaseGoodsSearch;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

/**
 * GoodsController implements the CRUD actions for CatalogBaseGoods model.
 */
class AmoController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
                        'actions' => ['index', 'update', 'create'],
                        'allow' => true,
                        'roles' => [
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
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new AmoSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new Franchisee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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
        if(!$model){
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru'=>'The requested page does not exist.']));
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
