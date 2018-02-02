<?php

namespace api\modules\v1\modules\restor\controllers;

use Yii;
use yii\web\Controller;
// use yii\mongosoft\soapserver\Action;
// use yii\httpclient\Client;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class CallbackController extends Controller {
    
    public $enableCsrfValidation = false;
   
    public function actionIndex() {
        
    $getr = Yii::$app->request->getRawBody();
    $file = Yii::$app->basePath . '/runtime/logs/rk_callback.log'; // Log file
   
    file_put_contents($file, PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);
    file_put_contents($file,PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND);
    file_put_contents($file, print_r($getr,true), FILE_APPEND);
    file_put_contents($file,PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND);
              
    }
    
    public function actionAgent() {
        (new \frontend\modules\clientintegr\modules\rkws\components\AgentHelper())->callback();
    }
    
    public function actionEdism() {
        (new \frontend\modules\clientintegr\modules\rkws\components\EdismHelper())->callback();
    }
    
        
    public function actionStore() {
        (new \frontend\modules\clientintegr\modules\rkws\components\StoreHelper())->callback();
    }
    
    public function actionProduct() {
        (new \frontend\modules\clientintegr\modules\rkws\components\ProductHelper())->callback();
    }
    
    public function actionWaybill() {
        (new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper())->callback();
    }

    public function actionProductgroup() {
        (new \frontend\modules\clientintegr\modules\rkws\components\ProductgroupHelper())->callback();
    }

}
