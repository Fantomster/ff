<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkTasks;
use api\common\models\RkDic;
use api\common\models\RkEdism;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EdismHelper extends AuthHelper
{

    // const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/edism";

    public function getEdism()
    {
        if (!$this->Authorizer()) {
            echo "Can't perform authorization";
            return;
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_munits" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/edism' . '">
    <PARAM name="object_id" val="' . $this->restr->code . '" />
    </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        $tmodel = new RkTasks();

        $tmodel->tasktype_id  = 34;
        $tmodel->acc          = $this->org;
        $tmodel->fid          = 1;
        $tmodel->guid         = $res['respcode']['taskguid'];
        $tmodel->fcode        = $res['respcode']['code'];
        $tmodel->version      = $res['respcode']['version'];
        $tmodel->isactive     = 1;
        $tmodel->created_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $tmodel->intstatus_id = 1;

        if (!$tmodel->save()) {
            echo "Ошибка валидации<br>";
            var_dump($tmodel->getErrors());
        }

        // Обновление словаря RkDic

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 4')->one();

        if (!$rmodel) {
            $this->log('RKDIC TMODEL NOT FOUND.');
            $this->log('Nothing has been saved.');
        } else {
            $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 2;
            $rmodel->obj_count    = 0;

            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else {
                $er3 = "Данные справочника успешно сохранены.(ID:" . $rmodel->id . " )";
            }
        }

        // var_dump($res);
        // die();
        return true;
    }

    public function callback()
    {
        $acc = 0;

        $getr   = Yii::$app->request->getRawBody();
        $myXML  = simplexml_load_string($getr);
        $gcount = 0;

        foreach ($myXML->ITEM as $itemgroup) {
            foreach ($itemgroup->attributes() as $c => $d) {
                // $array[$gcount][$c] = strval($d[0]);
                if ($c == 'rid') {
                    $grid = strval($d[0]);
                }
                if ($c == 'name') {
                    $grname = strval($d[0]);
                }
            }

            foreach ($itemgroup->MUNITS_LIST as $list) {
                foreach ($list->ITEM as $unit) {
                    $gcount++;
                    $array[$gcount]['group_rid']  = $grid;
                    $array[$gcount]['group_name'] = $grname;

                    foreach ($unit->attributes() as $a => $b) {
                        $array[$gcount][$a] = strval($b[0]);
                    }
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
        $posid   = strval($myXML['posid']);

        $this->log('=======EDISM==EVENT==START=================');
        $this->log(date("Y-m-d H:i:s") . ':REQUEST:');
        $this->log('===========================================');
        $this->log('CMDGUID:' . $cmdguid);
        $this->log('POSID:' . $posid);
        $this->log('*******************************************');
        $this->log(print_r($getr, true));
        $this->log('*******************************************');
        $this->log(print_r($array, true));
        $this->log('*****************ddd***********************');

        if (!empty($array) && !empty($cmdguid) && !empty($posid)) {
            // Заполнение tasks
            $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

            if (!$tmodel) {
                $this->log('=======EDISM==EVENT==START=================');
                $this->log(date("Y-m-d H:i:s") . ':REQUEST:');
                $this->log('===========================================');
                $this->log('CMDGUID:' . $cmdguid);
                $this->log('POSID:' . $posid);
                $this->log('*******************************************');
                $this->log(print_r($getr, true));
                $this->log('*******************************************');
                $this->log(print_r($array, true));
                $this->log('*******************************************');
                $this->log('TASK TMODEL NOT FOUND.!' . $cmdguid . '!');
                $this->log('Nothing has been saved.');
                exit;
            }

            $tmodel->intstatus_id = 3;
            $tmodel->isactive     = 0;
            $tmodel->callback_at  = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            $acc = $tmodel->acc;

            if (!$tmodel->save()) {
                $er2 = $tmodel->getErrors();
            } else {
                $er2 = "Данные task успешно сохранены (ID:" . $tmodel->id . " )";
            }

            // Заполнение units

            $this->log('Start arrange units model.');

            $icount = 0;

            foreach ($array as $a) {

                $checks = RkEdism::find()->andWhere('acc = :acc', [':acc' => $acc])
                        ->andWhere('rid = :rid', [':rid' => $a['rid']])
                        ->one();
                if (!$checks) {
                    $amodel             = new \api\common\models\RkEdism();
                    $amodel->acc        = $acc; // $tmodel->acc; 
                    $amodel->rid        = $a['rid'];
                    $amodel->denom      = $a['name'];
                    $amodel->group_rid  = $a['group_rid'];
                    $amodel->group_name = $a['group_name'];

                    // $amodel->ratio = $a['type'];
                    $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                    if (!$amodel->save()) {
                        $er = $amodel->getErrors();
                        $this->log('!!!' . $er);
                    } else {
                        $er = "Данные MUNITS успешно сохранены.(ID:" . $amodel->id . " )";
                    }
                }

                $icount++;
            }
        }

        // Обновление словаря RkDic

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 4')->one();

        if (!$rmodel) {
            $this->log('RKDIC TMODEL NOT FOUND.');
            $this->log('Nothing has been saved.');
        } else {
            $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 6;
            $rmodel->obj_count    = $icount;

            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
            } else {
                $er3 = "Данные справочника успешно сохранены.(ID:" . $rmodel->id . " )";
            }
        }

        if (empty($cmdguid)) {
            $cmdguid = 'пусто';
        }
        if (empty($posid)) {
            $posid = 'пусто';
        }
        if (empty($array)) {
            $array = array(0 => '0');
        }

        $this->log('=======AGENT==EVENT==START=================');
        $this->log(date("Y-m-d H:i:s") . ':REQUEST:');
        $this->log('===========================================');
        $this->log('CMDGUID:' . $cmdguid);
        $this->log('POSID:' . $posid);
        $this->log('*******************************************');
        $this->log(print_r($getr, true));
        $this->log('*******************************************');
        $this->log(print_r($array, true));
        $this->log('*******************************************');
        $this->log(print_r($er2, true));
        $this->log(print_r($er3, true));
        $this->log('============EVENT END======================');
    }

}
