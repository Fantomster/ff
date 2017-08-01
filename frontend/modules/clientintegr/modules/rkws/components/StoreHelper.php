<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkStore;
use api\common\models\RkDic;

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
     
          // Обновление словаря RkDic
    
        $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$this->org])->andWhere('dictype_id = 2')->one();
    
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
     
    // var_dump($res);
     
     return true;
    
    }
    
    public function callback()
    {       
    
    $getr = Yii::$app->request->getRawBody();
    
    
    $myXML   = simplexml_load_string($getr);
    $gcount = 0;        
    
    foreach ($myXML->STOREGROUP as $storegroup) {
            $gcount++;
            foreach($storegroup->attributes() as $c => $d) {
                if ($c == 'rid')  $arr[$gcount]['rid'] = strval($d[0]);  
                if ($c == 'name') $arr[$gcount]['name'] = strval($d[0]); 
                if ($c == 'parent')  $arr[$gcount]['parent'] = strval($d[0]); 
            }
            
            $arr[$gcount]['type'] = 1;
            $iparent = $gcount;
            
            $ridarray[$arr[$gcount]['rid']] = $gcount;
                    
                foreach ($storegroup->STORE as $store) {
                    $gcount++;
                          
                        foreach($store->attributes() as $a => $b) {
                          $arr[$gcount][$a] = strval($b[0]);
                        }
                    $arr[$gcount]['type'] = 2;
                    $arr[$gcount]['parent'] = $iparent;
                    
                }
    }
    
    // $arr2=$arr;
    
    foreach ($arr as $key => $value) {
        
        if ($value['type'] == '1' and ($value['parent']) != '') {
            
            $sval = $value['parent'];
           
            file_put_contents('runtime/logs/callback.log',$key.':'.$sval.PHP_EOL, FILE_APPEND); 
            
            // $value['parent']=$ridarray[$sval];
            $arr[$key]['parent'] = $ridarray[$sval];
            
            file_put_contents('runtime/logs/callback.log',':'.print_r($arr[$key]['parent'],true).PHP_EOL, FILE_APPEND); 
            
           
        }
        
    }
    
    //file_put_contents('runtime/logs/callback.log','++++++++++A2++++++++++++'.PHP_EOL, FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log',print_r($arr2,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log','++++++++++A1++++++++++++'.PHP_EOL , FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log',print_r($arr,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log','++++++++++EX++++++++++++'.PHP_EOL , FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log',print_r($ridarray,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents('runtime/logs/callback.log','++++++++++EX++++++++++++'.PHP_EOL , FILE_APPEND); 
    //exit;
    
    
    
    /* Рабочая версия без дерева
     * 
    foreach ($myXML->STOREGROUP as $storegroup) {
            foreach($storegroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid=strval($d[0]);
                if ($c == 'name') $grname=strval($d[0]);
            //    if ($c == 'parent') $grparent=strval($d[0]);
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
    */
    
    $cmdguid = $myXML['cmdguid']; 
    $posid = $myXML['posid']; 
    
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
            
            
     // Заполнение складов с деревом
            
        $icount =0;     
       
        foreach ($arr as $key => $a)   {
                       
            $amodel = new RkStoretree();
            
            $amodel->acc = $acc;
            $amodel->rid = $a['rid'];
            $amodel->denom = $a['name'];
            $amodel->prnt = $a['parent'];
            $amodel->type = $a['type'];
            $amodel->fid = $key;
            $amodel->version = 1;
            
            
            
        //    $amodel->agent_type = $a['type'];
            $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');  
            
            if (!$amodel->save()) {
                $er = $amodel->getErrors();
            } else $er = "Данные складов успешно сохранены.(ID:".$amodel->id." )";
            
            
                
            $icount++;
         
        }
            
       
            
     // Заполнение складов
     /* Заполнение складов рабочая версия без дерева       
      * 
        $icount =0;     
       
        foreach ($array as $a)   {
            
                    $checks = RkStore::find()->andWhere('acc = :acc',[':acc' => $acc])
                                        ->andWhere('rid = :rid',[':rid' => $a['rid']])                                           
                                        ->one();
                if (!$checks) {
            
            $amodel = new RkStore();
            
            $amodel->acc = $acc;
            $amodel->rid = $a['rid'];
            $amodel->denom = $a['name'];
        //    $amodel->agent_type = $a['type'];
            $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');  
            
            if (!$amodel->save()) {
                $er = $amodel->getErrors();
            } else $er = "Данные складов успешно сохранены.(ID:".$amodel->id." )";
            
                }
                
            $icount++;
         
        }
       */
    }
    
     // Обновление словаря RkDic
    
    $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$acc])->andWhere('dictype_id = 2')->one();
    
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
    file_put_contents('runtime/logs/callback.log',print_r($er,true) , FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',print_r($er,true) , FILE_APPEND); 
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'============EVENT END======================'.PHP_EOL,FILE_APPEND);   
              
    }

}
