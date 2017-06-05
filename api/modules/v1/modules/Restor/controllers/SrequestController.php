<?php

namespace api\modules\v1\modules\restor\controllers;

use Yii;
use yii\web\Controller;
// use yii\mongosoft\soapserver\Action;
use yii\httpclient\Client;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */

class SrequestController extends Controller {
    
    public $enableCsrfValidation = false;
    
    protected $authenticated = false;
    
    private $sessionId = '';
    private $username;
    private $password;
    
        
    
    public function actionIndex() {
        
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
    
    $restr = "199990046";
    
    $cook = 'C7C28BF23A769A7C6DDBFCE967EF2F848644A528624F9344CF05E237AF4969E'.
            '9BCCC961EC2EEC157A4DD0348A94B0CE19D14A036255718D52634F86DE24C4C7'.
            '9F09540B67EA78BFE823E5E086B28697898EE4CCB4F025006285DA75C18480C2'.
            'F2AD54F5F546A52603C8B7D1466D4442EF040871DADEC9F4CC8D4D024D612F14D'.
            '03C05B4402E3149CB99E133154C2ED9F0F399E06EC42829E9A2B1C09B3F3D1DE0'.
            'F3115832F31264F6CC8984BD15D45A365088B7D44C28789465A256AC9002F44C9'.
            '8F6AF1A9E5DEF4132F7EF5A1097EAFA4D2764983D076173396ECD49C5007B20D7'.
            '4BBE6CE24499C1D31F80F6DA9947004E276E31B807CC6050A1CBCEF4B1F0091E83'.
            '06ABD7D66462C7A2788C276F9FF69533F38B630BD628E3FD626B5582AF5B0AFE01'. 
            '7A3E579AB7998AD9E15EB8A79B2123B3C96F71AAD58290CC1F1700DAA92C3717B01'.
            '6427860E6D88BFAAB754630590E3082248C45A5648E8196E09F366';
    
    

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
    
    $fp = fopen('runtime/logs/http-request1.log', 'w');
    
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
    curl_setopt($ch, CURLOPT_STDERR,$fp);

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
    
    return $this->render('index'  ,[
                   'myXML' => $myXML,
                   'objectinfo' => $objectinfo,
                   'data' => $data,
                   'info' => $info,
                   'res'  => $res,
         
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
