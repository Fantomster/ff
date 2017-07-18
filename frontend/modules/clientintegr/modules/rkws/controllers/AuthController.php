<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkSession;
use api\common\models\RkWserror;
use api\common\models\RkAccess;
use common\models\User;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;


// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class AuthController extends Controller {
    
    
        
    public function actionIndex() {
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index');
        } else {
            return $this->render('index');
        }     
        
    }
     
public function actionSendlogin() {
    
    $org = User::findOne(Yii::$app->user->id)->organization_id;
    
    $res = ApiHelper::sendAuth($org);
    
    $errd = RkWserror::find()->andwhere('code = :code',[':code' =>$res['respcode']])->one()->denom;
              
        return $this->render('index'  ,[
            'res'  => $res,
            'errd' => $errd,
        ]);

}  
    


  function XML2Array(\SimpleXMLElement $parent)
{
    $array = array();

    foreach ($parent as $name => $element) {
        ($node = & $array[$name])
            && (1 === count($node) ? $node = array($node) : 1)
            && $node = & $node[];

        $node = $element->count() ? XML2Array($element) : trim($element);
    }

    return $array;
}
 
  
   
}
