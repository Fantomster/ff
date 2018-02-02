<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkCategory;
use api\common\models\RkDicconst;
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

class ProductHelper extends AuthHelper
{

    //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/product";

    public function getProduct()
    {
        if (!$this->Authorizer()) {

            echo "Can't perform authorization";
            return;
        }

        $guid = UUID::uuid4();

        $defGoodGroup = RkDicconst::findOne(['denom' => 'defGoodGroup'])->getPconstValue();
        $dGroups = '';

        foreach (explode(',', $defGoodGroup) as $group) {

            $smodel = RkCategory::find()->andWhere('id = :group',['group' => $group])->one();

            $dGroups .= '<PARAM name="goodgroup_rid" val="' . $smodel->rid . '" />';
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goodgroups" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/product' . '" timeout="3600">
    <PARAM name="object_id" val="' . $this->restr->code . '" />' .
    $dGroups . '<PARAM name="include_goods" val="1" />
    </RQ>';


        $res = ApiHelper::sendCurl($xml, $this->restr);
        $isLog = new DebugHelper();

        $isLog->setLogFile('../runtime/logs/rk_request_prod_' . date("Y-m-d_H-i-s").'.log');
        $isLog->logAppendString(print_r($xml,true));
        $isLog->logAppendString(print_r($res,true));


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

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 3')->one();

        if (!$rmodel) {
            $isLog->logAppendString('RKDIC TMODEL NOT FOUND. Nothing has been saved.');
        } else {

            $rmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 2;
            $rmodel->obj_count = 0;

            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else  { $er3 = "Данные справочника успешно сохранены.(ID:" . $rmodel->id . " )";
                    $isLog->logAppendString('Данные справочника DIC успешно сохранены.');
            }

        }

        // var_dump($res);

        return true;

    }

    /**
     *
     */
    public function callback()
    {

        $array = [];

        ini_set('MAX_EXECUTION_TIME', -1);

        $getr = Yii::$app->request->getRawBody();

        $myXML = simplexml_load_string($getr);

        $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid']; // Try to find guid in cmdguid or taskguid
        $posid = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';

        $isLog = new DebugHelper();

        $isLog->setLogFile('../runtime/logs/rk_callback_prod_' . date("Y-m-d_H-i-s").'_'.$cmdguid . '.log');

        $isLog->logAppendString('=========================================');
        $isLog->logAppendString(date("Y-m-d H:i:s") . ' : Product callback received ');
        $isLog->logAppendString('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
        $isLog->logAppendString('=========================================');
        $isLog->logAppendString(substr($getr, 0, 300));

    // Checking if the Task is active

        $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

        if (!$tmodel) {
            $isLog->logAppendString('ERROR:: Task with guid ' . $cmdguid . 'has not been found!!');
            echo "Не найдена задача с id: (" . $cmdguid . ")";
            exit;
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

        $gcount = 0;

        foreach ($myXML->ITEM as $goodsgroup) {

            foreach ($goodsgroup->attributes() as $c => $d) {
                if ($c == 'rid') $grid = strval($d[0]);
                if ($c == 'name') $grname = strval($d[0]);
                if ($c == 'parent') $grparent = strval($d[0]);
            }
            foreach ($goodsgroup->GOODS_LIST as $glist) {

                foreach ($glist->ITEM as $item) {

                    foreach ($item->attributes() as $a => $b) {
                        if ($a == 'rid') {
                            $prid = strval($b[0]);
                        }
                        if ($a == 'name') {
                            $prname = strval($b[0]);
                        }
                    }

                    foreach ($item->MUNITS->MUNIT as $unit) {
                        $gcount++;
                        $array[$gcount]['group_rid'] = $grid;
                        $array[$gcount]['group_name'] = $grname;
                        $array[$gcount]['group_parent'] = $grparent;
                        $array[$gcount]['product_rid'] = $prid;
                        $array[$gcount]['product_name'] = $prname;

                        foreach ($unit->attributes() as $e => $h) {
                            if ($e == 'rid') $array[$gcount]['unit_rid'] = strval($h[0]);
                            if ($e == 'name') $array[$gcount]['unit_name'] = strval($h[0]);
                        }

                    }

                }

            }


        }

    // Update task after XML

        if (!$tmodel->setCallbackXML()) {
            $isLog->logAppendString('ERROR:: Task after XML parsing cannot be saved!!');
            exit;
        } else {
            $isLog->logAppendString('SUCCESS:: Task after XML successfully saved!');
        }

        // Заполнение номенклатуры

        $icount = 0;
        $scount = 0;

        foreach ($array as $a) {

            $checks = RkProduct::find()->andWhere('acc = :acc', [':acc' => $acc])
                ->andWhere('rid = :rid', [':rid' => $a['product_rid']])
                ->andWhere('unit_rid = :unit_rid', [':unit_rid' => $a['unit_rid']])
                ->one();

            if ($checks == null) {

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
                    $isLog->logAppendString('ERROR:: Product ' . $amodel->rid . 'cannot be saved - ' . $er);
                }

                $scount++;
            }
            $icount++;
        }

        $isLog->logAppendString('SUCCESS:: Products saved: ' . $scount);

        $tmodel->rcount = $icount;
        $tmodel->intstatus_id = RkTasks::INTSTATUS_DICOK;


    // Обновление словаря RkDic
    
        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 3')->one();
    
        if (!$rmodel) {
            $isLog->logAppendString('ERROR:: Dictionary to update products is not found.');
            exit;
        }

        $fcount = RkProduct::find()->andWhere('acc= :org_id', [':org_id' => $acc])->count('*');

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

    }
}

