<?php

namespace backend\controllers;

use common\models\Organization;
use common\models\Request;
use common\models\RequestCallback;
use common\models\RequestCallbackSearch;
use common\models\RequestSearch;
use Yii;
use common\models\Role;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class RequestController extends Controller
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
                        'actions' => ['index', 'view', 'update', 'update-callback', 'delete-callback'],
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
     * Displays general settings
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RequestSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = "01.02.2017";
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("RequestSearch")) {
            $params['RequestSearch'] = Yii::$app->request->post("RequestSearch");
        }

        $dataProvider = $searchModel->search($params, null);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial("index", compact('searchModel', 'dataProvider'));
        } else {
            return $this->render("index", compact('searchModel', 'dataProvider'));
        }
    }


    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionView($id)
    {
        if (!Request::find()->where(['id' => $id])->exists()) {
            return $this->redirect("list");
        }
        $request = Request::find()->where(['id' => $id])->one();
        $author = Organization::findOne(['id' => $request->rest_org_id]);

        $searchModel = new RequestCallbackSearch();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("RequestCallbackSearch")) {
            $params['RequestCallbackSearch'] = Yii::$app->request->post("RequestCallbackSearch");
        }

        $dataCallback = $searchModel->search($params);
        return $this->render("view", compact('request', 'author', 'dataCallback', 'searchModel'));
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = Request::findOne($id);
        if(!$model){
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru'=>'The requested page does not exist.']));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->render('/request/update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdateCallback($id, $request_id)
    {
        $model = RequestCallback::find()->with('organization')->where(['id'=>$id])->one();
        $suppliersArray = (new Organization())->getSuppliers(0, true);
        if(!$model){
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $request_id]);
        } else {
            return $this->render('/request/update-callback', [
                'model' => $model,
                'suppliersArray' => $suppliersArray
            ]);
        }
    }


        public function actionDeleteCallback($id)
    {
        RequestCallback::findOne($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

}
