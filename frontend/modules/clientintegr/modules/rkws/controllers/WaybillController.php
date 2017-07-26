<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class WaybillController extends \frontend\modules\clientintegr\controllers\DefaultController {
    
        
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
    
    
    
    public function actionSendws() {
        
   //  $resres = ApiHelper::getAgents();     
        
        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        $res->sendWaybill();
                
            
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
