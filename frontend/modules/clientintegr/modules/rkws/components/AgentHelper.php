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

class AgentHelper extends AuthHelper {
    
  //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/agent";
    
    public function getAgents () {
    if (!$this->Authorizer()) {
       
      echo "Can't perform authorization";
      return;
    }    
    
    $guid = UUID::uuid4();
    
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_corrs" tasktype="any_call" guid="'.$guid.'" timeout="600" callback="'.Yii::$app->params['rkeepCallBackURL'].'/agent'.'">
    <PARAM name="object_id" val="'.$this->restr->salespoint.'" />
    </RQ>'; 
       
     $res = ApiHelper::sendCurl($xml,$this->restr);
     
     $tmodel = new RkTasks();
     
     $tmodel->tasktype_id = 32;
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
     
               // Обновление словаря RkDic
    
        $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$this->org])->andWhere('dictype_id = 1')->one();
    
        if (!$rmodel) {
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'RKDIC TMODEL NOT FOUND.'.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 

        } else {
            
            $rmodel->updated_at=Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
            $rmodel->dicstatus_id= 2;
            $rmodel->obj_count = 0;
    
            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else $er3 = "Данные справочника успешно сохранены.(ID:".$rmodel->id." )";
        }
     
     var_dump($res);
     
     return true;
    
    }
    
    public function callback()
    {       
    
    $acc =0;    
        
    $getr = Yii::$app->request->getRawBody();
    $myXML   = simplexml_load_string($getr);
    $gcount = 0;        
    
    foreach ($myXML->CORRGROUP as $corrgroup) {
            foreach($corrgroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid=strval($d[0]);
                if ($c == 'name') $grname=strval($d[0]);
            }
                foreach ($corrgroup->CORR as $corr) {
                    $gcount++;
                    $array[$gcount]['group_rid'] = $grid;
                    $array[$gcount]['group_name'] = $grname;
               
                        foreach($corr->attributes() as $a => $b) {
                          $array[$gcount][$a] = strval($b[0]);
                        }
                }
    }
    
    
    /*
    foreach ($myXML->RP as $rp) {
        
        $cmdguid = 'не зашли в RP';
        $posid = 'не зашли в RP';
        foreach($rp->attributes() as $a => $b) { 
                if ($a == 'cmdguid') $cmdguid=strval($b);
                if ($a == 'posid') $posid=strval($b);
        }
        
    }
    */
    
    $cmdguid = strval($myXML['cmdguid']); 
    $posid = strval($myXML['posid']); 
    
    if (!empty($array) && !empty($cmdguid) && !empty($posid))  {
        
     // Заполнение tasks
        $tmodel = RkTasks::find()->andWhere('guid= :guid',[':guid'=>$cmdguid])->one();
        
        if (!$tmodel) {
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'=======AGENT==EVENT==START================='.PHP_EOL,FILE_APPEND);  
        file_put_contents('runtime/logs/callback.log', PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'CMDGUID:'.$cmdguid.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'POSID:'.$posid.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
        file_put_contents('runtime/logs/callback.log',print_r($getr,true) , FILE_APPEND);    
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
        file_put_contents('runtime/logs/callback.log',print_r($array,true) , FILE_APPEND);    
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);      
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'TASK TMODEL NOT FOUND.!'.$cmdguid.'!'.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 
        exit;
        }
        
        $tmodel->intstatus_id = 3;
        $tmodel->isactive = 0;
        $tmodel->callback_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        
        $acc= $tmodel->acc;
        
            if (!$tmodel->save()) {
                $er2 = $tmodel->getErrors();
            } else $er2 = "Данные task успешно сохранены (ID:".$tmodel->id." )";
        
     // Заполнение контрагентов
        
        $icount =0;    
      
        foreach ($array as $a)   {
            
                            $checks = RkAgent::find()->andWhere('acc = :acc',[':acc' => $acc])
                                           ->andWhere('rid = :rid',[':rid' => $a['rid']])                                           
                                           ->one();
                if (!$checks) {
            
            $amodel = new RkAgent();
            
            $amodel->acc = $acc; // $tmodel->acc; 
            $amodel->rid = $a['rid'];
            $amodel->denom = $a['name'];
            $amodel->agent_type = $a['type'];
            $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');  
            
            if (!$amodel->save()) {
                $er = $amodel->getErrors();
            } else $er = "Данные контрагентов успешно сохранены.(ID:".$amodel->id." )";
            
                }
                
            $icount++;
         
        }
        
    }
    
    // Обновление словаря RkDic
    
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
    
    if (empty($er)) $er = 'пусто';     
    if (empty($er3)) $er3 = 'пусто'; 
        
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'=======AGENT==EVENT==START================='.PHP_EOL,FILE_APPEND);  
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
    file_put_contents('runtime/logs/callback.log',print_r($er2,true) , FILE_APPEND);    
    file_put_contents('runtime/logs/callback.log',print_r($er3,true) , FILE_APPEND);  
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'============EVENT END======================'.PHP_EOL,FILE_APPEND);   
 //   file_put_contents('runtime/logs/callback.log',PHP_EOL.$tmodel->guid.PHP_EOL,FILE_APPEND);            
    }

}
