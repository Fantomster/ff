<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkAccess;
use yii;
use api\common\models\RkSession;
use XMLReader;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ApiHelper  {
    
    
    /*public function checkAuth() {
    
        if (Yii::$app->request->isPjax) {
            //return $this->renderPartial('index');
        } else {
            
        $org = User::findOne(Yii::$app->user->id)->organization_id;
        
        $restr = RkAccess::find()->andwhere('org= :org',[':org' => $org])->one();
        
        $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="'.$restr->salespoint.'"/>
        </RQ>';  
        
        $res = self::sendCurl($xml,$restr);
        
      //  $errd = RkWserror::find()->andwhere('code = :code',[':code' =>$res['respcode']['code']])->one()->denom;
              
        return $res;
        
        }    
    
    }    */
    
    /*public function checkAuthBool() {
    
        if (Yii::$app->request->isPjax) {
            //return $this->renderPartial('index');
        } else {
            
        $org = User::findOne(Yii::$app->user->id)->organization_id;
        
        $restr = RkAccess::find()->andwhere('org= :org',[':org' => $org])->one();
        
        $xml = '<?xml version="1.0" encoding="utf-8" ?>
        <RQ cmd="get_objectinfo">
        <PARAM name="object_id" val="'.$restr->salespoint.'"/>
        </RQ>';  
        
        $res = self::sendCurl($xml,$restr);
        
      if ($res['respcode']['code'] == '0') {
          return true;
      } else {
          return false;
      }
          
              
        return $res;
        
        }    
    
    }   */
    
    /*
    public function getAgents () {
        
    $org = User::findOne(Yii::$app->user->id)->organization_id;
    $restr = RkAccess::find()->andwhere('org= :org',[':org' => $org])->one();
        
    if (!$check = self::checkAuthBool()) {
       
        $auth = self::sendAuth($org);
        
        if (!$auth['respcode'] == '0') {
            
            echo "Can't perform authorization";
            var_dump ($auth['$resp']);
        }
        
    }    
    
    $guid = UUID::uuid4();
          
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_corrs" tasktype="any_call" guid="'.$guid.'" callback="https://api.f-keeper.ru/api/web/v1/restor/callback">
    <PARAM name="object_id" val="'.$restr->salespoint.'" />
    </RQ>'; 
    
     $res = self::sendCurl($xml,$restr);
     
     $tmodel = new RkTasks();
     
     $tmodel->tasktype_id = 27;
     $tmodel->acc = $restr->fid;
     $tmodel->fid = 1;
     $tmodel->guid = $res['respcode']['taskguid'];
     $tmodel->fcode = $res['respcode']['code'];
     $tmodel->version = $res['respcode']['version'];
     $tmodel->isactive = 1;
     $tmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd H:i:s'); 
     $tmodel->intstatus_id = 1; 
     
     if (!$tmodel->save()) {
      
         var_dump($tmodel->getErrors());
     }
     
     var_dump($tmodel);
    
    }

    */
    public static function sendCurl($xml,$restr) {
        
    $url = "http://ws-w01m.ucs.ru/WSClient/api/Client/Cmd";
    
        if (empty($restr)) {
                   echo "SendCurl.Access is not found :(";
                   exit;
        }
    
    $sess = RkSession::find()->andwhere('acc= :acc',[':acc'=>$restr->fid])->andwhere('sysdate() between fd and td')->one();
    
    if (!$sess) {
                 echo "Session is not found :(";
                 exit;
    }
    
    
    $cook = $sess->cook;
    
        if (empty($cook)) {
                   echo "SendCurl.Session is not found :(";
                   exit;
        }
    
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
    
    
    /*
    
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
    
   //  var_dump($array);
    
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
*/
    
    public static function xml2array($xml) {

        $arr = array();

        foreach ($xml->children() as $k => $r) {

            if (count($r->children()) == 0) {
            //    if ($xml->$k->count() == 1) {
            //        $arr[$r->getName()] = strval($r);
                    
            //        $atts_object = $r->attributes();
            //        $atts_array = (array) $atts_object;
            //        $arr[$r->getName()][]=$atts_array;
                    
                  //  foreach ($r->attributes as $a => $b) {
                  //   $arr[$r->getName()]['@attributes'] = [$a => $b]; 
                  //  }    
                    
            //    } else {
                    $arr[$r->getName()][] = strval($r);
                    
                    $atts_object = $r->attributes();
                    $atts_array = (array) $atts_object;
                    $arr[$r->getName()][][]=$atts_array;
            //    }//Endif
            } else {
          
                $atts_object = $r->attributes();
                $atts_array = (array) $atts_object;
                $arr[$r->getName()][]=$atts_array;
                
                $arr[$r->getName()][] = self::xml2array($r);
            }//Endif
            
        }//Endofreach

        return $arr;
    }
    
    
}

