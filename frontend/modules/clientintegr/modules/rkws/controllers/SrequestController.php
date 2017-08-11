<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\web\Controller;
use api\common\models\RkSession;
use api\common\models\RkWserror;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkAccess;
use common\models\User;


// use yii\mongosoft\soapserver\Action;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class SrequestController extends Controller {
    
    
        
    public function actionIndex() {
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index');
        } else {
            return $this->render('index');
        }     
        
    }
    
    public function actionCheck() {
        
        if (Yii::$app->request->isPjax) {
            //return $this->renderPartial('index');
        } else {

        $result = ApiHelper::checkAuth();
        
        $errd = RkWserror::find()->andwhere('code = :code',[':code' =>$result['respcode']['code']])->one()->denom;
              
        return $this->render('index'  ,[
            'res'  => $result,
            'errd' => $errd,
        ]);
        
        }     
        
    }
    

    
    
     
       public function actionCheck2() {
        
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
    
    $restr = "199990046";
    
    $model = RkSession::find()->where('id=1')->one();
    $cook = $model->cook;
        
    // $cook = '8AC676108E295CBD193F9FD1D92D97E95DB023F2C4715BBAE4E73FF47CBDCA9463F8443FC5A0119BADF2BB57A40A9FF33653C1FAE71FD3FD2CC3592B2250356D965390F5931C26522575A500EE42B2999DA3468B07A0C908FEF94AE6C7E0DD618F2890EF88AB125223F5DF9ACBE36F988CCDE622DAACEB2B03BB5E34A2D4E1A06184DFDCEBF11821F874C7A211491B021C365FCBE1BB3D304E264627C74B8BC1D986BF1E80AE01AECBDD150BD5B3179B6714BF8213001E3B983708AEF70764161CF254F3F2B9512FFC06955EEA2DDE841438B21E20F8448F0E1BCCBFEC4C4BCF33DD6F70ED5F2CCDEDBBCD6A4F5FB344F4301D98F381EC42024DD5E82877135EDB167188E4E20A0D5FC3EB328CE15942B23E680CADBDFF7EFB9F0D535FFC02BD8F322F90254DA19442170E9FDFE3A2BCFCFE06C2C79B50407E2B37ACD8BB4A21286532D379A7A2A31DAD9B46A11BB2B2DE97D00C5189837788BC91744DEBF405';
    
    

    $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="'.$restr.'"/>
        </RQ>';    
       
    // setcookie('_ASPXAUTH',$cook);
    
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );
/*
    echo "<hr>";
    var_dump($xml);
    echo "<hr>";
  */
    
   // $fp = fopen('runtime/logs/http-request1.log', 'w');
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt ($ch, CURLOPT_COOKIE, ".ASPXAUTH=".$cook.";"); 
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
  //  curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    
    
    $info = curl_getinfo($ch);
    // echo "Request result:<br>"; 
    // var_dump($info);
    // echo "<hr>";
    // echo "Response:<br>";
    // echo $data;
    // echo "<hr><hr><hr>";
    // var_dump ($data);
    
    
   // $myXML = new \SimpleXMLElement($data);
    $myXML   = simplexml_load_string($data);
   // $array = $this->XML2Array($myXML);
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    

    /*
    foreach ($myobj->xpath('//OBJECTINFO') as $obj) {
    echo 'Объект с id ', $obj->id, ', имя ', $obj->name, ', адрес:', $obj->address, PHP_EOL;
    }
    */
    /*
    $myXML = new \DOMDocument('1.0', 'utf-8');
    
    $myXML->load($data);
    */
   // $root = $myXML->documentElement;
    
   //  $objects = $myXML->childNodes;
    
    // var_dump($myXML);
    
    // var_dump($array);
    
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
    /*
    foreach ($objects as $object) {
    
    $id = $object->getAttribute('id');
    $name = $object->getAttribute('name');
    $address = $object->getAttribute('address');
    
    $restors[] = array('id' => $id, 'name' => $name, 'address' => $address);
    }
    
    print_r($restors);
    
    */
    var_dump ($array);
    
    echo ApiHelper::parse('bugaga');
    
   //  if ($obj[code$errd = RkWserror::find()->andWhere('code='.$obj['code'])->one()->denom;
    
    
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
