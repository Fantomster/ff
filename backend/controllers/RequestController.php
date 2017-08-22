<?php

namespace backend\controllers;

use common\models\Organization;
use common\models\Request;
use common\models\RequestCallback;
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
                        'actions' => ['index', 'view'],
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
        $dataCallback = new ActiveDataProvider([
            'query' => RequestCallback::find()->where(['request_id' => $id])->orderBy('id DESC'),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
        return $this->render("view", compact('request', 'author', 'dataCallback'));
    }
}
