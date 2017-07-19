<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkAccess;
use yii;
use api\common\models\RkSession;
use XMLReader;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ApiHelper  {
    
    
public function sendCmd($cmd, $restr, $org) {
    
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
  
    $sess = RkSession::find()->andwhere('acc= :acc',[':acc'=>$restr->fid])->andwhere('sysdate() between fd and td')->one();
    $cook = $sess->cook;
    
    // var_dump ($cook);
    
    $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="'.$cmd.'">
        <PARAM name="object_id" val="'.$restr->salespoint.'"/>
        </RQ>';   
  
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );
  
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
       
    $myXML   = simplexml_load_string($data);
   // $array = $this->XML2Array($myXML);
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    
     var_dump($array);
    
    if ($array['Error']) {
        
    $objectinfo = ['Статус'=>'Ошибка'];        
    $respcode = $array['Error']['@attributes'];
    
    } else {
        
    $objectinfo = $array['RP']['OBJECTINFO']['@attributes'];    
    $respcode = $array['RP']['@attributes'];
    }
        
    
    if(curl_errno($ch))
    print curl_error($ch);
    else
    curl_close($ch);
    
    return ['resp' => $objectinfo, 'respcode' => $respcode];
    
}

    
public function sendAuth($org) {
    
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Login";
    
    $restrModel = RkAccess::find()->andwhere('sysdate() between fd and td and org= :org',[':org' => $org])->limit(1)->one();
       
    $licReq = $restrModel->lic;
    $rlogin = $restrModel->login;
    $rpass =  $restrModel->password;
    $rtoken = $restrModel->token;
                  
    $usrReq = base64_encode($rlogin.';'.strtolower(md5($rlogin.$rpass)).';'.strtolower(md5($rtoken)));
    
    $xml ='<?xml version="1.0" encoding="UTF-8"?><AUTHCMD key="'.$licReq.'" usr="'.$usrReq.'"/>';
   
    $headers = array(
        "Content-type: application/xml; charset=utf-8",
        "Content-length: " . strlen($xml),
        "Connection: close", 
    );
   
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 1); // Раскомментировать в случае дебага, 
  //  иначе header лезет в $data строкой и не получается XML (xsupervisor 04.07.2017
    
    curl_setopt($ch, CURLOPT_VERBOSE, true);
  //  curl_setopt($ch, CURLOPT_STDERR,$fp);

    $data = curl_exec($ch); 
    $info = curl_getinfo($ch);
    
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
    $cookies = array();
    
    foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
    }
        
    $cook = $cookies['_ASPXAUTH'];
    
    $partx= Substr($data, strpos($data,'<?xml'));
    
    $myXML   = simplexml_load_string($partx);   
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    
    $respcode = $array['Error']['@attributes']['code'];
    
    if (!$respcode === null) {
        $respcode = $array['RP']['@attributes']['code'];       
    }
    
    $objectinfo = $array['Error']['@attributes']  ;
    
    if ($cook && $respcode === '0') { 
        
    $sess = RkSession::find()->andwhere('acc= :acc',[':acc'=>$restrModel->fid])->andwhere('sysdate() between fd and td')->one();
    
    $sessmax = RkSession::find()->max('fid');   
    
    $newsess = new RkSession();
    
        $newsess->cook = $cook;
        $newsess->fd= Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd h:i:s');    
        $newsess->td= Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd h:i:s');
        $newsess->acc = $restrModel->fid;
        $newsess->status = 1;
                  
        if ($sess) {
        
            $sess->td = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd h:i:s');
            $sess->status = 0;     
            $newsess->fid = $sess->fid;
            $newsess->ver = $sess->ver+1; 
        
                $transaction = Yii::$app->db_api->beginTransaction();
    
                if ($sess->save(false) &&  $newsess->save(false)) {
    
                    $transaction->commit();
                } else {
    
                    var_dump($sess->getErrors());
                    var_dump($newsess->getErrors());
        
                    $transaction->rollback();
                    exit;
                }
        
        } else {
           $newsess->fid = $sessmax +1;
           $newsess->ver =1; 
           
            if (!$newsess->save(false)) {
                var_dump($newsess->getErrors());
                exit;
        }

        
        }
        
    }        

    /*
    
    $myXML   = simplexml_load_string($data);
   // $array = $this->XML2Array($myXML);
    $array = json_decode(json_encode((array) $myXML), 1);
    $array = array($myXML->getName() => $array);
    
    $respcode = $array['Error']['@attributes']['code'];
    
    var_dump($respcode);
    */
    /*
    if ($respcode == 0) {
    
    $sess = RkSession::find()->andwhere('acc= :acc',[':acc'=>$restrModel->fid])->andwhere('sysdate() between fd and td')->one();
    
    $sessmax = RkSession::find()->max('fid');    
    
    $newsess = new RkSession();
    
    if($sess) {

    $sess->td = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd h:i:s');
    $sess->status = 0;
    $newsess->fd= Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd h:i:s');    
    $newsess->fid = $sess->fid;
    $newsess->acc = $sess->acc;
    $newsess->ver = $sess->ver+1; 
        
    } else {
       
    $newsess->fd= Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd h:i:s');
    $newsess->fid = $sessmax+1;
    $newsess->acc = $restrModel->fid;
    $newsess->ver = 1; 
    }
    
    $newsess->td= Yii::$app->formatter->asDate('2030-01-01 23:59:59', 'yyyy-MM-dd h:i:s');
    $newsess->cook = 
    $newsess->status = 1;
     
    if ($sess) {
    
    $transaction = Yii::$app->db_api->beginTransaction();
    
    if ($sess->save(false) &&  $newsess->save(false)) {
    
        $transaction->commit();
    } else {
    
        var_dump($sess->getErrors());
        var_dump($newsess->getErrors());
        
        $transaction->rollback();
        exit;
    }
    
    
    } else {
        if (!$newsess->save(false)) {
            var_dump($newsess->getErrors());
            exit;
        }
    }
    
  
    $objectinfo = $array['RP']['OBJECTINFO']['@attributes'];    
    $respcode = $array['RP']['@attributes'];
    
    } else {
    $objectinfo = ['Статус'=>'Ошибка'];        
    $respcode = $array['Error']['@attributes'];   
    }
    */
    if(curl_errno($ch))
    print curl_error($ch);
    else
    curl_close($ch);
    
     return ['resp' => $objectinfo, 'respcode' => $respcode];
    
}

    
}

