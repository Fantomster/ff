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
   
    file_put_contents('runtime/logs/callback.log', PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log', print_r($getr,true), FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND);     
              
    }
    
    public function actionAgent() {
        (new \frontend\modules\clientintegr\modules\rkws\components\AgentHelper())->callback();
    }
    
        
    public function actionStore() {
        (new \frontend\modules\clientintegr\modules\rkws\components\StoreHelper())->callback();
    }
    
    public function actionProduct() {
        (new \frontend\modules\clientintegr\modules\rkws\components\ProductHelper())->callback();
    }

}
