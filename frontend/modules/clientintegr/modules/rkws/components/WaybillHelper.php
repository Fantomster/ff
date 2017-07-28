<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkAgent;
use api\common\models\RkDic;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WaybillHelper extends AuthHelper {
    
    const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/waybill";
    
    public function sendWaybill ($id) {
    if (!$this->Authorizer()) {
       
      echo "Can't perform authorization";
      return;
    }    
    
    $guid = UUID::uuid4();
    
    $wmodel = \api\common\models\RkWaybill::findOne(['id' => $id]);
    
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="'.$guid.'" callback="'.self::CALLBACK_URL.'">
    <PARAM name="object_id" val="'.$this->restr->salespoint.'" />
    <DOC date="'.Yii::$app->formatter->asDatetime($wmodel->doc_date, "php:Y.m.d").'" corr="'.$wmodel->corr_rid.'" store="'.$wmodel->store_rid.'" active="0"'
            . ' duedate="1" note="'.$wmodel->note.'" textcode="'.$wmodel->text_code.'" numcode="'.$wmodel->num_code.'">';           
    
   $recs = \api\common\models\RkWaybilldata::find()->andWhere('waybill_id = :wid',[':wid' => $id])->asArray(true)->all();
   
    foreach($recs as $rec) {
       
       $xml .='<ITEM rid="'.$rec["product_rid"].'" quant="'.($rec["quant"]*1000).'" mu="'.$rec["munit_rid"].'" sum="'.($rec['sum']*1000).'" vatrate="1800" />';
    }
   
   // var_dump($recs);
       
    $xml .= '</DOC>'
            . '</RQ>';
    
    
    /*
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="'.$guid.'" callback="'.self::CALLBACK_URL.'">
    <PARAM name="object_id" val="'.$this->restr->salespoint.'" />
    <DOC date="2017-07-12" corr="8" store="3" active="0" duedate="1" note="текст примечания" textcode="fk" numcode="5379">
    <ITEM rid="4" quant="48000" mu="2" sum="1290000" vatrate="1800" />
    <ITEM rid="3" quant="12345" mu="1" sum="1290000" vatrate="1800" />
    </DOC>
    </RQ>'; 
    */
          
     $res = ApiHelper::sendCurl($xml,$this->restr);
     
    // var_dump($res);
     
     
     $tmodel = new RkTasks();   
     
     $tmodel->tasktype_id = 33;
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
     
    // $wmodel = 
     
     // Обновление статуса выгрузки накладной 
     
     return true;
    
    }
    
    public function callback()
    {       
    
    $acc =0; 
    $stat =0;
        
    $getr = Yii::$app->request->getRawBody();
    $myXML   = simplexml_load_string($getr);
    $gcount = 0;        
    
    
    if(!isset($myXML->ERROR)) {
        
        $cmdguid = strval($myXML['cmdguid']); 
        $posid = strval($myXML['posid']); 
        $stat = 3;
        
        foreach ($myXML->DOC as $doc) {
            foreach($doc->attributes() as $a => $b) {
                $array[$gcount][$a] = strval($b[0]);                
            }
       
        }
        
    } else {
        
        $cmdguid = strval($myXML['taskguid']); 
        $stat = 4;
        foreach ($myXML->ERROR as $doc) {
            foreach($doc->attributes() as $a => $b) {
                $array[$gcount][$a] = strval($b[0]);                
            }

        }
    }
    
    
    
    
    if (!empty($array) && !empty($cmdguid))  { 
        
     // Заполнение tasks
        $tmodel = RkTasks::find()->andWhere('guid= :guid',[':guid'=>$cmdguid])->one();
        
        if (!$tmodel) {
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'=======WAYBILL==EVENT==START================='.PHP_EOL,FILE_APPEND);  
        file_put_contents('runtime/logs/callback.log', PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'CMDGUID:'.$cmdguid.PHP_EOL,FILE_APPEND); 
     //   file_put_contents('runtime/logs/callback.log',PHP_EOL.'POSID:'.$posid.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
        file_put_contents('runtime/logs/callback.log',print_r($getr,true) , FILE_APPEND);    
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
        file_put_contents('runtime/logs/callback.log',print_r($array,true) , FILE_APPEND);    
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);      
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'TASK TMODEL NOT FOUND.!'.$cmdguid.'!'.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 
        exit;
        }
        
        $tmodel->intstatus_id = $stat;
        $tmodel->isactive = 0;
        $tmodel->callback_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        
        $acc= $tmodel->acc;
        
            if (!$tmodel->save()) {
                $er2 = $tmodel->getErrors();
            } else $er2 = "Данные task успешно сохранены (ID:".$tmodel->id." )";
        
     // Заполнение контрагентов
        /*
        $icount =0;    
      
        foreach ($array as $a)   {
            
            $amodel = new RkAgent();
            
            $amodel->acc = $acc; // $tmodel->acc; 
            $amodel->rid = $a['rid'];
            $amodel->denom = $a['name'];
            $amodel->agent_type = $a['type'];
            $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');  
            
            if (!$amodel->save()) {
                $er = $amodel->getErrors();
            } else $er = "Данные контрагентов успешно сохранены.(ID:".$amodel->id." )";
            
            $icount++;
         
        }
        */
    }
    
    // Обновление словаря RkDic
    /*
    $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$acc])->andWhere('dictype_id = 1')->one();
    
        if (!$rmodel) {
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'RKDIC TMODEL NOT FOUND.'.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 

        } else {
            
            $rmodel->updated_at=Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
            $rmodel->dicstatus_id= 6;
            $rmodel->obj_count = $icount;
    
            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else $er3 = "Данные справочника успешно сохранены.(ID:".$rmodel->id." )";
        }
        
    */
   
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
        
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'=======WAYBILL==EVENT==START================='.PHP_EOL,FILE_APPEND);  
    file_put_contents('runtime/logs/callback.log', PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'CMDGUID:'.$cmdguid.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'POSID:'.$posid.PHP_EOL,FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents('runtime/logs/callback.log',print_r($getr,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents('runtime/logs/callback.log',print_r($array,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
 //   file_put_contents('runtime/logs/callback.log',print_r($er,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',print_r($er2,true) , FILE_APPEND);    
 //   file_put_contents('runtime/logs/callback.log',print_r($er3,true) , FILE_APPEND);  
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'============EVENT END======================'.PHP_EOL,FILE_APPEND);   
 //   file_put_contents('runtime/logs/callback.log',PHP_EOL.$tmodel->guid.PHP_EOL,FILE_APPEND);            
    }

}
