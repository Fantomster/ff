<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use common\models\User;
use Yii;
use yii\web\Controller;
use api\common\models\RkAgent;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class AgentController extends \frontend\modules\clientintegr\controllers\DefaultController {

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'agent-mapping' => [                                       // identifier for your editable column action
                'class' => EditableColumnAction::className(),     // action class name
                'modelClass' => RkAgent::className(),                // the model for the record being edited
                'outputValue' => function ($model, $attribute, $key, $index) {
                    $vendor = $model->vendor;
                    return isset($vendor) ? $vendor->name : null;      // return any custom output value if desired
                },
                'outputMessage' => function($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true,                        // show model validation errors after save
                'errorOptions' => ['header' => '']  ,              // error summary HTML options
                'postOnly' => true,
                'ajaxOnly' => true,
            ]
        ]);
    }

    public function actionIndex() {
        
        $searchModel = new RkAgentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        } else {
            return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        }     
        
    }
    
    public function actionView($id)
    {
        return $this->render('view', [
            'dataProvider' => $this->findModel($id),
        ]);
    }
    
    public function actionGetws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new \frontend\modules\clientintegr\modules\rkws\components\AgentHelper();
        $res->getAgents();
        
            $this->redirect('/clientintegr/rkws/default');
            
    }
      
        protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {
            
            $model = RkAgent::find()->andWhere('acc = :acc',[':acc' => $dmodel->org_id]);
            
            $dataProvider = new ActiveDataProvider([
                                        'query' => $model,
                                        'sort' => false ]);
            
            return $dataProvider;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Формирование списка поставщиков по введенным символам
     * @param null $term
     * @return mixed
     * @throws \yii\db\Exception
     */
    public function actionAgentAutocomplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!is_null($term)) {
            $user = User::findOne(\Yii::$app->user->id);
            $organisation_id = $user->organization_id;
            $sql = "SELECT id, `name` as text FROM organization where `name` LIKE '%$term%' and type_id = 2 and id in (SELECT supp_org_id FROM relation_supp_rest where rest_org_id = $organisation_id and deleted = 0)";
            $db = \Yii::$app->db;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }
   
}
