<?php

namespace backend\controllers;

use api\common\models\RkServicedata;
use Yii;
use common\models\Organization;
use common\models\Role;
use backend\models\OrganizationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use frontend\modules\clientintegr\modules\rkws\components\ServiceHelper;
use api\common\models\RkService;

//use frontend\modules\clientintegr\modules\rkws\components\ServiceHelper;

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class RkwsController extends Controller {

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
                        'actions' => ['index', 'view', 'getws','autocomplete','create'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new \api\common\models\RkServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query0 = "select `created` from `rk_actions` where `id` = '1'";
        $a = Yii::$app->db_api->createCommand($query0)->queryScalar();
        $data_last_license = $a;

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'data_last_license' => $data_last_license,
        ]);
    }

    /**
     * Displays a single Organization model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }
    
    public function actionGetws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new ServiceHelper();
        $res->getObjects();
        
        $this->redirect('index');
            
    }

//    /**
//     * Creates a new Organization model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new Organization();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }
//
    /**
     * Updates an existing Organization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findDataModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
         //   return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    public function actionCreate($service_id) {

        $model = new RkServicedata();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //   return $this->redirect(['view', 'id' => $model->id]);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
                'service_id' => $service_id,
            ]);
        }
    }
    
        public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
       // $out = ['results' => ['id' => '0', 'text' => 'Создать контрагента']];
        if (!is_null($term)) {
            $query = new \yii\db\Query;

           // $query->select("`id`, CONCAT(`inn`,`denom`) AS `text`")
              $query->select(['id'=>'id','text' => 'CONCAT("(ID:",`id`,") ",`name`)']) 
                    ->from('organization')
                    ->where('type_id = 1')  
                    ->andwhere("id like :id or `name` like :name",[':id' => '%'.$term.'%', ':name' => '%'.$term.'%'])
                    ->limit(20);

            $command = $query->createCommand();
         //   $command->db = Yii::$app->db_api;
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } 
        // $out['results'][] = ['id' => '0', 'text' => 'Создать контрагента'];
        return $out;
    }

//    /**
//     * Deletes an existing Organization model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = \api\common\models\RkService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function findDataModel($id) {
        if (($model = \api\common\models\RkServicedata::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    protected function checkIntegrRK () {
        
        $arr = RkServicedata::find()->select('org')->asArray->all();
                
        if (in_array(User::findOne([Yii::$app->user->id])->organization_id,$arr)) {
            return true; // Ресторан есть в доступах к лицензии (даже если она неактивна
        } else {
            return false; // Ресторана нет в сервисах
        }
            
    }

}
