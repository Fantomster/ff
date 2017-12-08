<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkProduct;
use api\common\models\RkDic;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ProductHelper extends AuthHelper {
    
  //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/product";
    
    public function getProduct () {
    if (!$this->Authorizer()) {
       
      echo "Can't perform authorization";
      return;
    }    
    
    $guid = UUID::uuid4();
          
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goodgroups" tasktype="any_call" guid="'.$guid.'" callback="'.Yii::$app->params['rkeepCallBackURL'].'/product'.'" timeout="3600">
    <PARAM name="object_id" val="'.$this->restr->code.'" />
    <PARAM name="goodgroup_rid" val="1" />
    <PARAM name="include_goods" val="1" />
    </RQ>'; 
       
     $res = ApiHelper::sendCurl($xml,$this->restr);
     
     
     $tmodel = new RkTasks();
     
     $tmodel->tasktype_id = 23;
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
    
        $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$this->org])->andWhere('dictype_id = 3')->one();
    
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
    
    foreach ($myXML->ITEM as $goodsgroup) {
            foreach($goodsgroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid=strval($d[0]);
                if ($c == 'name') $grname=strval($d[0]);
                if ($c == 'parent') $grparent=strval($d[0]);
            }
                foreach ($goodsgroup->GOODS_LIST as $glist) {
                    
                    foreach ($glist->ITEM as $item) {
                        
                        foreach($item->attributes() as $a => $b) {
                            if ($a == 'rid') { $prid=strval($b[0]); }
                            if ($a == 'name') { $prname=strval($b[0]); }
                        }    
                        
                            foreach ($item->MUNITS->MUNIT as $unit) {
                            $gcount++;
                                $array[$gcount]['group_rid'] = $grid;
                                $array[$gcount]['group_name'] = $grname;
                                $array[$gcount]['group_parent'] = $grparent;
                                $array[$gcount]['product_rid'] = $prid;
                                $array[$gcount]['product_name'] = $prname;
                                
                                foreach($unit->attributes() as $e => $h) {
                                    if ($e == 'rid') $array[$gcount]['unit_rid'] = strval($h[0]);
                                    if ($e == 'name') $array[$gcount]['unit_name'] = strval($h[0]);
                                }
                                
                            }

                        }
                        
            }
                                     
                
    }



    if (empty($array)) {

        foreach ($myXML->ERROR as $err) {

            foreach($err->attributes() as $e => $h) {
                if ($e == 'code') $array['code'] = strval($h[0]);
                if ($e == 'text') $array['text'] = strval($h[0]);
            }

        }

    }



    /* Работает без едизм
    foreach ($myXML->ITEM as $goodsgroup) {
            foreach($goodsgroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid=strval($d[0]);
                if ($c == 'name') $grname=strval($d[0]);
                if ($c == 'parent') $grparent=strval($d[0]);
            }
                foreach ($goodsgroup->GOODS_LIST as $glist) {
                    
                    foreach ($glist->ITEM as $item) {
                        $gcount++;
                        $array[$gcount]['group_rid'] = $grid;
                        $array[$gcount]['group_name'] = $grname;
                        $array[$gcount]['group_parent'] = $grparent;
                        
                        foreach($item->attributes() as $a => $b) {
                          $array[$gcount][$a] = strval($b[0]);
                        }
                        
                    }
                                     
                }
    }
    */
    
    $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid'];
    $posid = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-' ;

    
    if (!empty($array) && !empty($cmdguid)) {

        // Заполнение tasks
        $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();


        if (!$tmodel) {
            file_put_contents('runtime/logs/callback.log', PHP_EOL . '=======PRODUCT==EVENT==START=================' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . date("Y-m-d H:i:s") . ':REQUEST:' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . '===========================================' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . 'CMDGUID:' . $cmdguid . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . 'POSID:' . $posid . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . '*******************************************' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', print_r($getr, true), FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . '*******************************************' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', print_r($array, true), FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . '*******************************************' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . 'TASK TMODEL NOT FOUND.!' . $cmdguid . '!' . PHP_EOL, FILE_APPEND);
            file_put_contents('runtime/logs/callback.log', PHP_EOL . 'Nothing has been saved.' . PHP_EOL, FILE_APPEND);

            echo "Не найдена задача с id: ".$cmdguid;
            exit;
        }

        $tmodel->intstatus_id = 3;
        $tmodel->isactive = 0;
        $tmodel->callback_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $tmodel->wsstatus_id = isset($array['code']) ? $array['code'] : 0;

        $acc = $tmodel->acc;

        if (!$tmodel->save()) {
            $er2 = $tmodel->getErrors();
        } else $er2 = "Данные task успешно сохранены (ID:" . $tmodel->id . " )";

        // Заполнение номенклатуры

        if (!empty($array[1]['group_rid'])) {

        $icount = 0;

        foreach ($array as $a) {


            $checks = RkProduct::find()->andWhere('acc = :acc', [':acc' => $acc])
                ->andWhere('rid = :rid', [':rid' => $a['product_rid']])
                ->andWhere('unit_rid = :unit_rid', [':unit_rid' => $a['unit_rid']])
                ->one();
            if (!$checks) {

                $amodel = new RkProduct();

                $amodel->acc = $acc;
                $amodel->rid = $a['product_rid'];
                $amodel->denom = $a['product_name'];
                $amodel->unit_rid = $a['unit_rid'];
                $amodel->unitname = $a['unit_name'];
                $amodel->group_rid = $a['group_rid'];
                $amodel->group_name = $a['group_name'];

                //    $amodel->agent_type = $a['type'];
                $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                if (!$amodel->save()) {
                    $er = $amodel->getErrors();
                } else $er = "Данные продуктов успешно сохранены.(ID:" . $amodel->id . " )";

              }
             $icount++;
          }
                        echo "Данные номенклатуры успешно распознаны и сохранены. (Count: ".sizeof($array).")";
        } else {
            if (isset($array['code']))

                echo "Код ошибки принят и сохранен.";
            else
                echo "Неизвестная ошибка";
        }


    }
    
       
    // Обновление словаря RkDic
    
    $rmodel= RkDic::find()->andWhere('org_id= :org_id',[':org_id'=>$acc])->andWhere('dictype_id = 3')->one();
    
        if (!$rmodel) {
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'RKDIC TMODEL NOT FOUND.'.PHP_EOL,FILE_APPEND); 
        file_put_contents('runtime/logs/callback.log',PHP_EOL.'Nothing has been saved.'.PHP_EOL,FILE_APPEND); 

        } else {
            
            $rmodel->updated_at=Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss'); 
            $rmodel->dicstatus_id= 6;
            $rmodel->obj_count = isset($icount) ? $icount : 0;
    
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
    $er = (isset($array['code'])) ? 'Ошибка :'.$array['code'].', '.$array['text'] : '-Код ошибки не опознан-';
        
    file_put_contents('runtime/logs/callback.log',PHP_EOL.'=========PRODUCT==EVENT==START==============='.PHP_EOL,FILE_APPEND);
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
