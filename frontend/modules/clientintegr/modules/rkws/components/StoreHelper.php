<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkStore;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StoreHelper extends AuthHelper {
    
    const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/store";
    
    public function getStore () {
    if (!$this->Authorizer()) {
       
      echo "Can't perform authorization";
      return;
    }    
    
    $guid = UUID::uuid4();
          
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_stores" tasktype="any_call" guid="'.$guid.'" callback="'.self::CALLBACK_URL.'">
    <PARAM name="object_id" val="'.$this->restr->salespoint.'" />
    </RQ>'; 
       
     $res = ApiHelper::sendCurl($xml,$this->restr);
     
     
     $tmodel = new RkTasks();
     
     $tmodel->tasktype_id = 25;
     $tmodel->acc = $this->org;
     $tmodel->fid = 1;
     $tmodel->guid = $res['respcode']['taskguid'];
     $tmodel->fcode = $res['respcode']['code'];
     $tmodel->version = $res['respcode']['version'];
     $tmodel->isactive = 1;
     $tmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
     $tmodel->intstatus_id = 1; 
     
     if (!$tmodel->save()) {
         echo "Ошибка валидации<br>";
         var_dump($tmodel->getErrors());
     }
     
     var_dump($res);
    
    }
    
    public function callback()
    {       
    
    $getr = Yii::$app->request->getRawBody();
    
    
    $myXML   = simplexml_load_string($getr);
    $gcount = 0;        
    
    
    foreach ($myXML->STOREGROUP as $storegroup) {
            foreach($storegroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid=strval($d[0]);
                if ($c == 'name') $grname=strval($d[0]);
            }
                foreach ($storegroup->STORE as $store) {
                    $gcount++;
                    $array[$gcount]['group_rid'] = $grid;
                    $array[$gcount]['group_name'] = $grname;
               
                        foreach($store->attributes() as $a => $b) {
                          $array[$gcount][$a] = strval($b[0]);
                        }
                }
    }
    
    $cmdguid = $myXML['cmdguid']; 
    $posid = $myXML['posid']; 
    
    if (!empty($array) && !empty($cmdguid) && !empty($posid))  {
        
     // Заполнение tasks
     //   $tmodel = RkTasks::find()->andWhere('')
     //   
        
     // Заполнение складов
       
        foreach ($array as $a)   {
            
            $amodel = new RkStore();
            
            $amodel->acc = $this->org; // Потом взять из task, когда заработает сервер
            $amodel->rid = $a['rid'];
            $amodel->denom = $a['name'];
        //    $amodel->agent_type = $a['type'];
            $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');  
            
            if (!$amodel->save()) {
                $er = $amodel->getErrors();
            } else $er = "Данные складов успешно сохранены.(ID:".$amodel->id." )";
         
        }
       
    }
    
   
  //  $array = ApiHelper::xml2array($myXML);
  //  
  //  $array = json_decode(json_encode((array) $myXML), 1);
  //  $array = array($myXML->getName() => $array);
   
    /*
    foreach($array['CORRGROUP'] as $corgroup) {
        
        foreach ($corgroup['CORR'] as $cor) {
            
            
        }
        
    }
   */
    if (empty($cmdguid)) $cmdguid = 'пусто';     
    if (empty($posid)) $posid = 'пусто'; 
    if (empty($array)) $array=array(0 => '0');
        
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'=========STORE==EVENT==START==============='.PHP_EOL,FILE_APPEND);  
    file_put_contents('runtime/logs/callback.log', PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'CMDGUID:'.$cmdguid.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'POSID:'.$posid.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents('runtime/logs/callback.log',print_r($getr,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents('runtime/logs/callback.log',print_r($array,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents('runtime/logs/callback.log',print_r($er,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'============EVENT END======================'.PHP_EOL,FILE_APPEND);   
              
    }

}
