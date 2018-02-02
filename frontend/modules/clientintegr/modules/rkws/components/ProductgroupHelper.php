<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkStore;
use api\common\models\RkCategory;
use creocoder\nestedsets\NestedSetsBehavior;
use api\common\models\RkDic;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ProductgroupHelper extends AuthHelper {
    
  //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/store";
    
    public function getCategory () {
    if (!$this->Authorizer()) {
       
      echo "Can't perform authorization";
      return;
    }

    $guid = UUID::uuid4();

    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goodgroups" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/productgroup' . '" timeout="3600">
    <PARAM name="object_id" val="' . $this->restr->code . '" />    
    <PARAM name="include_goods" val="0" />
    </RQ>';

    $res = ApiHelper::sendCurl($xml, $this->restr);

    $isLog = new DebugHelper();

    $isLog->setLogFile('../runtime/logs/rk_request_prodgroup_' . date("Y-m-d_H-i-s").'.log');
     
     $tmodel = new RkTasks();
     
     $tmodel->tasktype_id = 11;
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
    
        $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$this->org])->andWhere('dictype_id = 5')->one();
    
        if (!$rmodel) {
            $isLog->logAppendString('RKDIC TMODEL NOT FOUND. Nothing has been saved.');

        } else {
            
            $rmodel->updated_at=Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
            $rmodel->dicstatus_id= 2;
            $rmodel->obj_count = 0;
    
            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else {
                $er3 = "Данные справочника успешно сохранены.(ID:".$rmodel->id." )";
                $isLog->logAppendString('Данные справочника DIC успешно сохранены.');
            }
        }
     
    // var_dump($res);
     
     return true;
    
    }
    
    public function callback()
    {

        $array = [];

        ini_set('MAX_EXECUTION_TIME', -1);

        $getr = Yii::$app->request->getRawBody();
        $acc=3243;

        $myXML = simplexml_load_string($getr);

        $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid']; // Try to find guid in cmdguid or taskguid
        $posid = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';

        if (!$cmdguid)
                $cmdguid = 'noGUID';

        $isLog = new DebugHelper();

        $isLog->setLogFile('../runtime/logs/rk_callback_pgroup_' . date("Y-m-d_H-i-s").'_'.$cmdguid . '.log');

        $isLog->logAppendString('=========================================');
        $isLog->logAppendString(date("Y-m-d H:i:s") . ' : Store callback received... ');
        $isLog->logAppendString('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
        $isLog->logAppendString('=========================================');
        // $isLog->logAppendString(substr($getr, 0, 600));
        $isLog->logAppendString(print_r($getr,1));

        // Checking if the Task is active

        $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

        if (!$tmodel) {
            $isLog->logAppendString('ERROR:: Task with guid ' . $cmdguid . 'has not been found!!');
            echo "Не найдена задача с id: (" . $cmdguid . ")";
            exit;
        } else {
            $isLog->logAppendString('-- Task with guid ' . $cmdguid . ' has been found.');
        }

        $acc = $tmodel->acc;
        $tmodel->isactive = 0;
        $tmodel->setCallbackStart();

        // Parsing XML for errors

        foreach ($myXML->ERROR as $err) {

            foreach ($err->attributes() as $e => $h) {
                if ($e == 'code') $array['code'] = strval($h[0]);
                if ($e == 'text') $array['text'] = strval($h[0]);
            }

        }

        if (isset($array['code'])) {  // We got external error

            $tmodel->intstatus_id = RkTasks::INTSTATUS_EXTERROR;
            $tmodel->wsstatus_id = $array['code'];
            $tmodel->retry = $tmodel->retry + 1;
            $tmodel->rcount = 0;

            if (!$tmodel->setCallbackEnd()) {
                $isLog->logAppendString('ERROR:: Task with external ERROR with guid ' . $cmdguid . 'cannot be saved!!');
                echo "Cannot save task (" . $cmdguid . ") with error: (" . $array['code'] . ")";
                exit;
            } else {
                $isLog->logAppendString('Task with external ERROR with guid ' . $cmdguid . 'successfully saved!');
                echo "Task with guid (" . $cmdguid . ") with error: (" . $array['code'] . ") successfully saved.";
                exit;
            }

        }

        // We got no errors. Try to parse XML with no external errors

        $gcount =0;

        $rress = Yii::$app->db_api->createCommand('UPDATE rk_storetree SET active=0 WHERE acc=:acc', [':acc' => $acc])->execute();

        foreach ($myXML->ITEM as $item) {

            $gcount++;

            foreach($item->attributes() as $c => $d) {
                if ($c == 'rid')  $arr[$gcount]['rid'] = strval($d[0]);
                if ($c == 'name') $arr[$gcount]['name'] = strval($d[0]);
                if ($c == 'parent')  $arr[$gcount]['parent'] = strval($d[0]);
            }

            if ($arr[$gcount]['parent'] === '') { // Корень дерева
                $rtree = new RkCategory(['name'=>$arr[$gcount]['name']]);
                $rtree->disabled =1;
                $rtree->rid = $arr[$gcount]['rid'];
                $rtree->acc = $acc;

                $rtree->makeRoot();

            } else {

                ${'rid'.$arr[$gcount]['rid']} = new RkCategory(['name'=>$arr[$gcount]['name']]);
                ${'rid'.$arr[$gcount]['rid']}->type = 1;
                ${'rid'.$arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                ${'rid'.$arr[$gcount]['rid']}->prnt = $arr[$gcount]['parent'];
                ${'rid'.$arr[$gcount]['rid']}->disabled = 1;
                ${'rid'.$arr[$gcount]['rid']}->acc = $acc;

                if ($arr[$gcount]['parent'] === '0' || !isset(${'rid'.$arr[$gcount]['parent']})) { // Цепляем к корню
                    ${'rid'.$arr[$gcount]['rid']}->prependTo($rtree);
                } else { // Дети некорня

                 ${'rid'.$arr[$gcount]['rid']}->prependTo(${'rid'.$arr[$gcount]['parent']});
                }


            }

        }


/*
            foreach($storegroup->attributes() as $c => $d) {
                if ($c == 'rid')  $arr[$gcount]['rid'] = strval($d[0]);
                if ($c == 'name') $arr[$gcount]['name'] = strval($d[0]);
                if ($c == 'parent')  $arr[$gcount]['parent'] = strval($d[0]);
            }

            $arr[$gcount]['type'] = 1;
            $iparent = $gcount;
            $ridarray[$arr[$gcount]['rid']] = $gcount;
            $spar = $arr[$gcount]['rid'];

            if ($arr[$gcount]['parent'] === '') { // Корень дерева
                $rtree = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                $rtree->disabled =1;
                $rtree->acc = $acc;
                $rtree->makeRoot();
            } else {
                ${'rid'.$arr[$gcount]['rid']} = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                ${'rid'.$arr[$gcount]['rid']}->type = 1;
                ${'rid'.$arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                ${'rid'.$arr[$gcount]['rid']}->prnt = $arr[$gcount]['parent'];
                ${'rid'.$arr[$gcount]['rid']}->disabled = 1;
                ${'rid'.$arr[$gcount]['rid']}->acc = $acc;



                if ($arr[$gcount]['parent'] === '0') { // Цепляем к корню
                    ${'rid'.$arr[$gcount]['rid']}->prependTo($rtree);
                } else { // Дети некорня
                    ${'rid'.$arr[$gcount]['rid']}->prependTo(${'rid'.$arr[$gcount]['parent']});
                }

                $icount++;
            }

            foreach ($storegroup->STORE as $store) {
                $gcount++;

                foreach($store->attributes() as $a => $b) {
                    $arr[$gcount][$a] = strval($b[0]);
                }
                $arr[$gcount]['type'] = 2;
                $arr[$gcount]['parent'] = $iparent;

                ${'srid'.$arr[$gcount]['rid']} = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                ${'srid'.$arr[$gcount]['rid']}->type = 2;
                ${'srid'.$arr[$gcount]['rid']}->prnt = $spar;
                ${'srid'.$arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                ${'srid'.$arr[$gcount]['rid']}->disabled = 0;
                ${'srid'.$arr[$gcount]['rid']}->acc = $acc;

                if ($spar === '0' || $spar === '') {
                    ${'srid'.$arr[$gcount]['rid']}->appendTo($rtree);
                } else {
                    ${'srid'.$arr[$gcount]['rid']}->appendTo(${'rid'.$spar});
                }

                $icount++;

            }
        }
*/
        // Update task after XML

        if (!$tmodel->setCallbackXML()) {
            $isLog->logAppendString('ERROR:: Task after XML parsing cannot be saved!!');
            exit;
        } else {
            $isLog->logAppendString('SUCCESS:: Task after XML successfully saved!');
        }

        $isLog->logAppendString('SUCCESS:: Categories saved');

        $tmodel->rcount = $gcount;
        $tmodel->intstatus_id = RkTasks::INTSTATUS_DICOK;


        // Обновление словаря RkDic

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 5')->one();

        if (!$rmodel) {
            $isLog->logAppendString('ERROR:: Dictionary to update categories is not found.');
            exit;
        }

        $fcount = RkStore::find()->andWhere('acc= :org_id', [':org_id' => $acc])->count('*');

        $rmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $rmodel->dicstatus_id = 6;
        $rmodel->obj_count = isset($fcount) ? $fcount : 0;

        if (!$rmodel->save()) {
            $er3 = $rmodel->getErrors();
            $isLog->logAppendString('ERROR:: Dictionary ' . $rmodel->id . 'cannot be saved - ' . $er3);
            exit;
        } else $isLog->logAppendString('SUCCESS:: Dictionary ' . $rmodel->id . ' is successfully saved.');

        $tmodel->intstatus_id = RkTasks::INTSTATUS_FULLOK;

        if (!$tmodel->setCallbackEnd()) {
            $isLog->logAppendString('ERROR:: Task status THE END cannot be saved!!');
            exit;
        } else {
            $isLog->logAppendString('SUCCESS:: All operations status is looking good');
            echo 'SUCCESS:: All operations status is looking good';
            exit;
        }

/*

    $getr = Yii::$app->request->getRawBody();
    $file = Yii::$app->basePath . '/runtime/logs/rk_callback_store.log'; // Log file

    $myXML   = simplexml_load_string($getr);
    $gcount = 0;        
    $acc = 3243;
  
        
    $cmdguid = $myXML['cmdguid']; 
    $posid = $myXML['posid']; 
    
    if (!empty($cmdguid) && !empty($posid))  {
  */
/*
     // Заполнение tasks
             $tmodel = RkTasks::find()->andWhere('guid= :guid',[':guid'=>$cmdguid])->one();
        
        if (!$tmodel) {
            $message = [
                '(Store event registered...)',
                'DATE: ' . date('d.m.Y H:i:s'),
                'CMDGUID: '. $cmdguid,
                'POSID: '. $posid,
                str_pad('', 200, '-') . PHP_EOL
            ];
        file_put_contents($file,print_r($getr,true) , FILE_APPEND);
        file_put_contents($file,PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
        file_put_contents($file,print_r($array,true) , FILE_APPEND);    
        file_put_contents($file,PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);      
        file_put_contents($file,PHP_EOL.'TASK TMODEL NOT FOUND.!'.$cmdguid.'!'.PHP_EOL,FILE_APPEND); 
        file_put_contents($file,PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 
        exit;
        }
        
        $tmodel->intstatus_id = 3;
        $tmodel->isactive = 0;
        $tmodel->callback_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        
        $acc= $tmodel->acc;

        
            if (!$tmodel->save()) {
                $er2 = $tmodel->getErrors();
            } else $er2 = "Данные task успешно сохранены (ID:".$tmodel->id." )";
            
        $icount =0;
        
      //  $sql = 'update rk_storetree set active = 0 where acc='.User::findOne([Yii::$app->user->id])->organization_id;
        
        $rress = Yii::$app->db_api->createCommand('UPDATE rk_storetree SET active=0 WHERE acc=:acc', [':acc' => $acc])->execute();
            
            
     // Заполнение складов с деревом
         /*   
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
            
       */
            
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

    }
*/
/*
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
            $spar = $arr[$gcount]['rid'];
            
            if ($arr[$gcount]['parent'] === '') { // Корень дерева
                $rtree = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                $rtree->disabled =1;
                $rtree->acc = $acc;
                $rtree->makeRoot();
            } else {
                    ${'rid'.$arr[$gcount]['rid']} = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                    ${'rid'.$arr[$gcount]['rid']}->type = 1;
                    ${'rid'.$arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                    ${'rid'.$arr[$gcount]['rid']}->prnt = $arr[$gcount]['parent'];
                    ${'rid'.$arr[$gcount]['rid']}->disabled = 1;
                    ${'rid'.$arr[$gcount]['rid']}->acc = $acc;
                    
                  
                   
                    if ($arr[$gcount]['parent'] === '0') { // Цепляем к корню
                        ${'rid'.$arr[$gcount]['rid']}->prependTo($rtree);
                        } else { // Дети некорня
                        ${'rid'.$arr[$gcount]['rid']}->prependTo(${'rid'.$arr[$gcount]['parent']});
                    }
                    
                    $icount++;
            }
                    
                foreach ($storegroup->STORE as $store) {
                    $gcount++;
                          
                        foreach($store->attributes() as $a => $b) {
                          $arr[$gcount][$a] = strval($b[0]);
                        }
                    $arr[$gcount]['type'] = 2;
                    $arr[$gcount]['parent'] = $iparent;
                    
                    ${'srid'.$arr[$gcount]['rid']} = new RkStoretree(['name'=>$arr[$gcount]['name']]);
                    ${'srid'.$arr[$gcount]['rid']}->type = 2;
                    ${'srid'.$arr[$gcount]['rid']}->prnt = $spar;
                    ${'srid'.$arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                    ${'srid'.$arr[$gcount]['rid']}->disabled = 0;
                    ${'srid'.$arr[$gcount]['rid']}->acc = $acc;
                    
                    if ($spar === '0' || $spar === '') {
                        ${'srid'.$arr[$gcount]['rid']}->appendTo($rtree);
                    } else {
                        ${'srid'.$arr[$gcount]['rid']}->appendTo(${'rid'.$spar});
                    }
                    
                    $icount++;
                    
                }
    }
  */
        
    // $arr2=$arr;
    
    /*
    
    foreach ($arr as $key => $value) {
        
        if ($value['type'] == '1' and ($value['parent']) != '') {
            
            $sval = $value['parent'];
           
            file_put_contents($file,$key.':'.$sval.PHP_EOL, FILE_APPEND); 
            
            // $value['parent']=$ridarray[$sval];
            $arr[$key]['parent'] = $ridarray[$sval];
            
            file_put_contents($file,':'.print_r($arr[$key]['parent'],true).PHP_EOL, FILE_APPEND); 
            
           
        }
        
    }
    */
    
    //file_put_contents($file,'++++++++++A2++++++++++++'.PHP_EOL, FILE_APPEND); 
    //file_put_contents($file,print_r($arr2,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents($file,'++++++++++A1++++++++++++'.PHP_EOL , FILE_APPEND); 
    //file_put_contents($file,print_r($arr,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents($file,'++++++++++EX++++++++++++'.PHP_EOL , FILE_APPEND); 
    //file_put_contents($file,print_r($ridarray,true).PHP_EOL , FILE_APPEND); 
    //file_put_contents($file,'++++++++++EX++++++++++++'.PHP_EOL , FILE_APPEND); 
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
    
    /*
    
     // Обновление словаря RkDic
    
    $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$acc])->andWhere('dictype_id = 2')->one();
    

        
        if (!$rmodel) {
        file_put_contents($file,PHP_EOL.'RKDIC TMODEL NOT FOUND.'.PHP_EOL,FILE_APPEND); 
        file_put_contents($file,PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 

        } else {
            
            $rmodel->updated_at=Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
            $rmodel->dicstatus_id= 6;
            $rmodel->obj_count = $icount;
            
        //    file_put_contents($file,PHP_EOL.print_r($rmodel,true).PHP_EOL,FILE_APPEND); 
            
        //    exit;
    
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
    /*    
    if (empty($cmdguid)) $cmdguid = 'пусто';     
    if (empty($posid)) $posid = 'пусто'; 
    if (empty($array)) $array=array(0 => '0');
        
    file_put_contents($file,PHP_EOL.'=========STORE==EVENT==START==============='.PHP_EOL,FILE_APPEND);  
    file_put_contents($file, PHP_EOL.date("Y-m-d H:i:s").':REQUEST:'.PHP_EOL, FILE_APPEND);   
    file_put_contents($file,PHP_EOL.'==========================================='.PHP_EOL,FILE_APPEND); 
    file_put_contents($file,PHP_EOL.'CMDGUID:'.$cmdguid.PHP_EOL,FILE_APPEND); 
    file_put_contents($file,PHP_EOL.'POSID:'.$posid.PHP_EOL,FILE_APPEND); 
    file_put_contents($file,PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents($file,print_r($getr,true) , FILE_APPEND);    
    file_put_contents($file,PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents($file,print_r($arr,true) , FILE_APPEND);    
    file_put_contents($file,PHP_EOL.'*******************************************'.PHP_EOL,FILE_APPEND);     
    file_put_contents($file,print_r($er,true) , FILE_APPEND);    
    file_put_contents($file,print_r($er,true) , FILE_APPEND); 
    file_put_contents($file,print_r($er,true) , FILE_APPEND); 
    file_put_contents($file,PHP_EOL.'============EVENT END======================'.PHP_EOL,FILE_APPEND);   
      */        
    }

}
