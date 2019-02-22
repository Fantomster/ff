<?php

namespace backend\controllers;

use backend\models\CurrencySearch;
use common\models\Currency;
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
class CurrencyController extends Controller
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
                        'actions' => ['index', 'update'],
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
        $searchModel = new CurrencySearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = Currency::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru' => 'The requested page does not exist.']));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/currency/index']);
        } else {
            return $this->render('/currency/update', [
                'model' => $model,
            ]);
        }
    }

}
