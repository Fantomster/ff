<?php

namespace backend\controllers;

use api\common\models\RkServicedata;
use common\models\RkActions;
use Yii;
use common\models\Organization;
use common\models\Role;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use api\common\models\RkService;

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class RkwsController extends Controller
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
                        'actions' => ['index', 'view', 'getws', 'autocomplete', 'create'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => ['update'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new \api\common\models\RkServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $rkActions = RkActions::findOne(['id' => 1]);
        $data_last_license = $rkActions->created;
        $dataProvider->pagination->pageParam = 'page_outer';

        return $this->render('index', [
            'searchModel'       => $searchModel,
            'dataProvider'      => $dataProvider,
            'data_last_license' => $data_last_license,
        ]);
    }

    /**
     * Displays a single Organization model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionGetws()
    {
        $vrem = date("Y-m-d H:i:s");
        $rkActions = RkActions::findOne(['id' => 1]);
        $rkActions->created = $vrem;
        $rkActions->save();

        $rkService = RkService::findOne(['code' => '199990046']);
        $a = $rkService->td;

        if ($a == '0001-01-05 00:00:00') {
            $rkService->td = '2100-01-01 00:00:00';
            $rkService->save();
        }

        $this->redirect('index');

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
        $model = $this->findDataModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreate($service_id)
    {

        $model = new RkServicedata();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model'      => $model,
                'service_id' => $service_id,
            ]);
        }
    }

    public function actionAutocomplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!is_null($term)) {
            $query = new \yii\db\Query;

            $query->select(['id' => 'o.id', 'text' => 'CONCAT("(ID:",o.id,") ",o.name)'])
                ->from('organization o')
                ->where('o.type_id = 1')
                ->andwhere("o.id like :id or o.name like :name", [':id' => '%' . $term . '%', ':name' => '%' . $term . '%'])
                ->limit(20);

            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
    }

    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \api\common\models\RkService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findDataModel($id)
    {
        if (($model = \api\common\models\RkServicedata::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function checkIntegrRK()
    {

        $arr = RkServicedata::find()->select('org')->asArray->all();

        if (in_array(User::findOne([Yii::$app->user->id])->organization_id, $arr)) {
            return true; // Ресторан есть в доступах к лицензии (даже если она неактивна
        } else {
            return false; // Ресторана нет в сервисах
        }

    }

}
