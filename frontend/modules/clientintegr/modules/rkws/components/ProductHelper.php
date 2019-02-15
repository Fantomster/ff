<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkCategory;
use api\common\models\RkDicconst;
use yii;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
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
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.not.auth', ['ru' => 'Не удалось авторизоваться на сервере R-Keeper.']));
        }

        $rguid = UUID::uuid4();

        $defGoodGroup = RkDicconst::findOne(['denom' => 'defGoodGroup'])->getPconstValue();
        $dGroups      = '';
        $currGroup    = 0;
        $groupArray   = $defGoodGroup ? explode(',', $defGoodGroup) : [0];
        $groupCount   = sizeof($groupArray);

        foreach ($groupArray as $group) { // Start cycle for groups
            $currGroup++;

            $smodel = RkCategory::find()->andWhere('id = :group', ['group' => $group])->one();

            if (isset($smodel))
                $dGroups = '<PARAM name="goodgroup_rid" val="' . $smodel->rid . '" />';
            else
                $dGroups = '<PARAM name="goodgroup_rid" val="0" />';

            $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goods" tasktype="any_call" guid="' . $rguid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/product' . '" timeout="3600">
    <PARAM name="object_id" val="' . $this->restr->code . '" />' .
                    $dGroups .
                    '</RQ>';

            $res = ApiHelper::sendCurl($xml, $this->restr);

            $this->log(print_r($xml, true));
            $this->log(print_r($res, true));

            $tmodel = new RkTasks();

            $tmodel->tasktype_id  = 22;
            $tmodel->acc          = $this->org;
            $tmodel->fid          = 1;
            $tmodel->guid         = $res['respcode']['taskguid'];
            $tmodel->fcode        = $res['respcode']['code'];
            $tmodel->version      = $res['respcode']['version'];
            $tmodel->isactive     = 1;
            $tmodel->created_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $tmodel->intstatus_id = 1;
            $tmodel->total_parts  = $groupCount;
            $tmodel->current_part = $currGroup;
            $tmodel->req_uid      = $rguid;

            if (!$tmodel->save()) {
                echo "Ошибка валидации<br>";
                var_dump($tmodel->getErrors());
            }
        }

        // Обновление словаря RkDic

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 3')->one();

        if (!$rmodel) {
            $this->log('RKDIC TMODEL NOT FOUND. Nothing has been saved.');
        } else {

            if ($tmodel->total_parts === $tmodel->current_part) {
                $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $rmodel->dicstatus_id = 2;
                $rmodel->obj_count    = 0;

                if (!$rmodel->save()) {
                    $er3 = $rmodel->getErrors();
                } else {
                    $er3 = "Данные справочника успешно сохранены.(ID:" . $rmodel->id . " )";
                    $this->log('Данные справочника DIC успешно сохранены.');
                }
            }
        }
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
        $posid   = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';

        $this->log('=========================================');
        $this->log(date("Y-m-d H:i:s") . ' : Product callback received ');
        $this->log('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
        $this->log('=========================================');
        //$this->log(substr($getr, 0, 300));
        $this->log(print_r($getr, 1));

        // Checking if the Task is active

        $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

        if (!$tmodel) {
            $this->log('ERROR:: Task with guid ' . $cmdguid . 'has not been found!!');
            echo "Не найдена задача с id: (" . $cmdguid . ")";
            exit;
        }

        $acc              = $tmodel->acc;
        $tmodel->isactive = 0;
        $tmodel->setCallbackStart();

        // Parsing XML for errors

        foreach ($myXML->ERROR as $err) {

            foreach ($err->attributes() as $e => $h) {
                if ($e == 'code')
                    $array['code'] = strval($h[0]);
                if ($e == 'text')
                    $array['text'] = strval($h[0]);
            }
        }

        if (isset($array['code'])) {  // We got external error
            $tmodel->intstatus_id = RkTasks::INTSTATUS_EXTERROR;
            $tmodel->wsstatus_id  = $array['code'];
            $tmodel->retry        = $tmodel->retry + 1;
            $tmodel->rcount       = 0;

            if (!$tmodel->setCallbackEnd()) {
                $this->log('ERROR:: Task with external ERROR with guid ' . $cmdguid . 'cannot be saved!!');
                echo "Cannot save task (" . $cmdguid . ") with error: (" . $array['code'] . ")";
                exit;
            } else {
                $this->log('Task with external ERROR with guid ' . $cmdguid . 'successfully saved!');
                echo "Task with guid (" . $cmdguid . ") with error: (" . $array['code'] . ") successfully saved.";
                exit;
            }
        }

        // We got no errors. Try to parse XML with no external errors

        $gcount = 0;

        foreach ($myXML->ITEM as $item) {

            foreach ($item->attributes() as $c => $d) {
                if ($c == 'rid')
                    $prid  = strval($d[0]);
                if ($c == 'name')
                    $pname = strval($d[0]);
            }

            foreach ($item->MUNITS as $munit) {

                foreach ($munit->MUNIT as $unit) {

                    foreach ($unit->attributes() as $c => $d) {
                        if ($c == 'rid')
                            $urid  = strval($d[0]);
                        if ($c == 'name')
                            $uname = strval($d[0]);
                    }

                    $gcount++;

                    $array[$gcount]['group_rid']  = 1;
                    $array[$gcount]['group_name'] = 'пока нет';
                    // $array[$gcount]['group_parent'] = $grparent;

                    $array[$gcount]['product_rid']  = $prid;
                    $array[$gcount]['product_name'] = $pname;
                    $array[$gcount]['unit_rid']     = $urid;
                    $array[$gcount]['unit_name']    = $uname;
                }
            }
        }

        /* Old version

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
         */
        // Update task after XML

        if (!$tmodel->setCallbackXML()) {
            $this->log('ERROR:: Task after XML parsing cannot be saved!!');
            exit;
        } else {
            $this->log('SUCCESS:: Task after XML successfully saved!');
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

                $amodel->acc        = $acc;
                $amodel->rid        = $a['product_rid'];
                $amodel->denom      = $a['product_name'];
                $amodel->unit_rid   = $a['unit_rid'];
                $amodel->unitname   = $a['unit_name'];
                $amodel->group_rid  = $a['group_rid'];
                $amodel->group_name = $a['group_name'];
                $amodel->is_active  = 1;

                //    $amodel->agent_type = $a['type'];
                $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                if (!$amodel->save()) {
                    $er = $amodel->getErrors();
                    $this->log('ERROR:: Product ' . $amodel->rid . 'cannot be saved - ' . $er);
                }
                $scount++;
            } else {
                $checks->is_active = 1;
                if (!$checks->save()) {
                    $er = $checks->getErrors();
                    $this->log('ERROR:: Product ' . $checks->rid . 'cannot be saved - ' . $er);
                }
            }
            $icount++;
        }

        $where = (new \yii\db\Query())
                ->where(['acc' => $acc])
                ->andWhere([
            '<',
            RkProduct::tableName() . ".updated_at",
            \gmdate('Y-m-d H:i:s', strtotime("-1 hour"))
        ]);
        RkProduct::updateAll(['is_active' => 0], $where);

        $this->log('SUCCESS:: Products saved: ' . $scount);

        $tmodel->rcount       = $icount;
        $tmodel->intstatus_id = RkTasks::INTSTATUS_DICOK;
        $tmodel->fcode        = 1;

        if (!$tmodel->save()) {
            $this->log('ERROR:: Task status THE END cannot be saved!!');
            exit;
        } else {
            $this->log('SUCCESS:: FCODE status is looking good');
        }

        // Обновление словаря RkDic

        if ($tmodel->isAllPartsReady($tmodel->req_uid)) { // If all parts are processed
            $this->log('All the parts are received...');
        }

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 3')->one();

        if (!$rmodel) {
            $this->log('ERROR:: Dictionary to update products is not found.');
            exit;
        }

        $fcount = RkProduct::find()->andWhere('acc= :org_id', [':org_id' => $acc])->andWhere('is_active = 1')->count('*');

        $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $rmodel->dicstatus_id = 6;
        $rmodel->obj_count    = isset($fcount) ? $fcount : 0;

        if (!$rmodel->save()) {
            $er3 = $rmodel->getErrors();
            $this->log('ERROR:: Dictionary ' . $rmodel->id . 'cannot be saved - ' . $er3);
            exit;
        } else
            $this->log('SUCCESS:: Dictionary ' . $rmodel->id . ' is successfully saved.');
        //}

        $tmodel->intstatus_id = RkTasks::INTSTATUS_FULLOK;

        if (!$tmodel->setCallbackEnd()) {
            $this->log('ERROR:: Task status THE END cannot be saved!!');
            exit;
        } else {
            $this->log('SUCCESS:: All operations status is looking good');
            echo 'SUCCESS:: All operations status is looking good';
            exit;
        }
    }

}
