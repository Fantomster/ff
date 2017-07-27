<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class WaybillController extends \frontend\modules\clientintegr\controllers\DefaultController {
    
    
    public function actions()
   {
       return ArrayHelper::merge(parent::actions(), [
           'edit' => [                                       // identifier for your editable action
               'class' => EditableColumnAction::className(),     // action class name
               'modelClass' => RkWaybilldata::className(),                // the update model class
               'outputValue' => function ($model, $attribute, $key, $index) {
                    $value = $model->$attribute;                 // your attribute value
                    if ($attribute === 'product_rid') {   
                        
                        $rkProd = \api\common\models\RkProduct::findOne(['id' => $value]);
                        $model->product_rid = $rkProd->rid;
                        $model->munit_rid = $rkProd->unit_rid;
                        $model->save(false);
                        return $rkProd->denom;       // return formatted value if desired
                    } 
                    return '';                                   // empty is same as $value
               },
               'outputMessage' => function($model, $attribute, $key, $index) {
                     return '';                                  // any custom error after model save
               },
               // 'showModelErrors' => true,                     // show model errors after save
               // 'errorOptions' => ['header' => '']             // error summary HTML options
               // 'postOnly' => true,
               // 'ajaxOnly' => true,
               // 'findModel' => function($id, $action) {},
               // 'checkAccess' => function($action, $model) {}
           ]
       ]);
   }
    
        
    public function actionIndex() {
        
        $searchModel = new \common\models\search\OrderSearch();
        
        $dataProvider = $searchModel->searchWaybill(Yii::$app->request->queryParams);
                
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
    
    public function actionMap($waybill_id) {
        
     $records = RkWaybilldata::find()->andWhere(['waybill_id' => $waybill_id]);
     
     $dataProvider = new ActiveDataProvider([           'query' => $records,
                                        'sort' => false,
                           ]);
                
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('indexmap',[
            'dataProvider' => $records,
        ]);
        } else {
            return $this->render('indexmap',[
            'dataProvider' => $dataProvider,
        ]);
        }     
        
    }
    
    public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
       // $out = ['results' => ['id' => '0', 'text' => 'Создать контрагента']];
        if (!is_null($term)) {
            $query = new \yii\db\Query;

           // $query->select("`id`, CONCAT(`inn`,`denom`) AS `text`")
              $query->select(['id'=>'id','text' => 'CONCAT(`denom`," (",unitname,")")']) 
                    ->from('rk_product')
                    ->where('acc = :acc',[':acc' => User::findOne(Yii::$app->user->id)->organization_id])  
                    ->andwhere("denom like :denom ",[':denom' => '%'.$term.'%'])
                    ->limit(20);

            $command = $query->createCommand();
            $command->db = Yii::$app->db_api;
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } 
      //  $out['results'][] = ['id' => '0', 'text' => 'Создать контрагента'];
        return $out;
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
          //  return $this->redirect(['view', 'id' => $model->id]);
            
            if ($model->getErrors()) {
                var_dump ($model->getErrors());
                exit;
            } 
            
            return $this->redirect(['index']);
            
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    
    public function actionCreate($order_id)
    {
        $model = new RkWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            if ($model->getErrors()) {
                var_dump ($model->getErrors());
                exit;
            } 
            
            return $this->redirect(['index']);
            
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    
    
    public function actionSendws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        $res->sendWaybill($id);
                
            
    }
    
    protected function findModel($id)
    {
        if (($model = RkWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
      
    

  
   
}
