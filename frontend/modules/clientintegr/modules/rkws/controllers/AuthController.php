<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkSession;
use api\common\models\RkWserror;


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
        
    $licReq = "TaS1MFk5aRk=tuKE2zLI2eqnCJnATjuErPNyIFl/vTQ1+IgJj7Rhx+nnoNq+k1K90kqofh4qDg+g4Lo4mlIg2tCQfxnDmitpzKkIyUIDFy4J6tud0pZf9nahgfFcwiGtZNFUM1I3h/J+Vu78vxp9wHkWRQ3sI9yy7A/o1QKKOyGi03S5/9TMA1v92TdYURdb8jdUcQJgui1dQIgHzE56O9OqV/DGVT5DhqjSfsvZIOmaj0+0FHJSrQZt7cO628h6UrA916dDTECb9fDWjprydt+oYPudzcwx02m7CmEDBSEn7CJcY+OE0y3+Q3vBUZNuEQ=="; 
    $rlogin = '5889';
    $rpass = 'uqbihcj';
    $rtoken = '48eabe9e-fc50-4b12-833c-ccc41480852d';
                  
    $usrReq = base64_encode($rlogin.';'.strtolower(md5($rlogin.$rpass)).';'.strtolower(md5($rtoken)));
    
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Login";

    $xml ='<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="'.$licReq.'" usr="'.$usrReq.'"/>';
   
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );

   // $fp = fopen('runtime/logs/http-request.log', 'w');
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
  //  curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    
//    $info = curl_getinfo($ch);

    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
    $cookies = array();
    
    foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
    }
    
   // echo "Cookies:<br>";
   // var_dump($cookies);
    
    $model = RkSession::find()->where('id=1')->one();
    $model->cook = $cookies['_ASPXAUTH'];
    
    if(!$model->save(false)) {
                            echo "Не могу сохранить cookie";
                            exit;
                             }
                      
     var_dump($data);
     exit;
                             
    $myXML   = simplexml_load_string($data);                         
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    
    
    
    $objectinfo = $array['RP']['OBJECTINFO'];
    
   // var_dump($objectinfo);
    
    if (!$objectinfo) {
        
            foreach ($array['Error'] as $obj) {
          $res = 'Ошибка: '.$obj['code'].'<br> Описание ошибки: '.$obj['Text'].PHP_EOL;
            }
        
    } else {
            
            foreach ($array['RP']['OBJECTINFO'] as $obj) {
          $res = 'Объект id: '.$obj['id'].'<br>имя: '.$obj['name'].'<br>адрес: '.$obj['address'].PHP_EOL;
            }
    
    }
    
    
    return $this->render('index'  ,[
                 //  'myXML' => $myXML,
                 //  'objectinfo' => $objectinfo,
                 //  'data' => $data,
                 //  'info' => $info,
                   'res'  => $res,
                   'errd' => $errd
         
               ]);

if(curl_errno($ch))
    print curl_error($ch);
else
    curl_close($ch);

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
