<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
// use api\common\models\RkAgent;
// use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkStoreSearch;
use api\common\models\RkStore;

// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class StoreController extends Controller {
    
        
    public function actionIndex() {
        
        $searchModel = new RkStoreSearch();
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
    
    public function actionGetws() {
        
    $res = new \frontend\modules\clientintegr\modules\rkws\components\StoreHelper();
    $res->getStore();
            
    }
      
    

  
   
}
