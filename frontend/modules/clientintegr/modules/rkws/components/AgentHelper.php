<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use Yii;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkTasks;
use api\common\models\RkAgent;
use api\common\models\RkDic;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AgentHelper extends AuthHelper
{

    // protected $callbackUrl = Yii::$app->params['rkeepCallBackURL']."/agent";

    public function getAgents()
    {
        if (!$this->Authorizer()) {
            echo "Can't perform authorization";
            return;
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_corrs" tasktype="any_call" guid="' . $guid . '" timeout="600" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/agent' . '">
    <PARAM name="object_id" val="' . $this->restr->code . '" />
    </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        $tmodel = new RkTasks();

        $tmodel->tasktype_id  = 32;
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

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 1')->one();

        if (!$rmodel) {
            $this->log('RKDIC TMODEL NOT FOUND.');
            $this->log('Nothing has been saved.');
        } else {
            $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 2;
            $rmodel->obj_count    = 0;

            if (!$rmodel->save()) { //wtf?
                $er3 = $rmodel->getErrors();
            } else {
                $er3 = "Данные справочника успешно сохранены.(ID:" . $rmodel->id . " )";
            }
        }

        // var_dump($res);

        return true;
    }

    public function callback()
    {
        $acc = 0;

        $getr = Yii::$app->request->getRawBody();

        $this->log('(Callback started)()()()()(');
        $this->log('!' . print_r($getr, true) . '!');
        $this->log('()()()()()(');

        $myXML  = simplexml_load_string($getr);
        $gcount = 0;
        $array = [];

        foreach ($myXML->CORRGROUP as $corrgroup) {
            foreach ($corrgroup->attributes() as $c => $d) {
                if ($c == 'rid') {
                    $grid   = strval($d[0]);
                }
                if ($c == 'name') {
                    $grname = strval($d[0]);
                }
            }
            foreach ($corrgroup->CORR as $corr) {
                $gcount++;
                $array[$gcount]['group_rid']  = $grid;
                $array[$gcount]['group_name'] = $grname;

                foreach ($corr->attributes() as $a => $b) {
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

        $cmdguid = strval($myXML['cmdguid']) ? strval($myXML['cmdguid']) : strval($myXML['taskguid']);
        $posid   = strval($myXML['posid']) ? strval($myXML['posid']) : 1;

        if (!empty($array) && !empty($cmdguid) && !empty($posid)) {

            // Заполнение tasks
            $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

            if (!$tmodel) {
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

            // Заполнение контрагентов

            $icount = 0;

            foreach ($array as $a) {

                $checks = RkAgent::find()->andWhere('acc = :acc', [':acc' => $acc])
                        ->andWhere('rid = :rid', [':rid' => $a['rid']])
                        ->one();
                if (!$checks) {

                    $amodel = new RkAgent();

                    //  $nameEnc = iconv('Windows-1252', 'Windows-1251', $a['name']);
                    //  $nameEnc = iconv('Windows-1252', 'utf8', $nameEnc);

                    $amodel->acc        = $acc; // $tmodel->acc; 
                    $amodel->rid        = $a['rid'];
                    // $amodel->denom = (RkDicconst::findOne(['denom' => 'useWinEncoding'])->getPconstValue() === 1) ? $nameEnc : $a['name'];
                    $amodel->denom      = $a['name'];
                    $amodel->agent_type = $a['type'];
                    $amodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                    if (!$amodel->save()) {
                        $er = $amodel->getErrors();
                    } else {
                        $er = "Данные контрагентов успешно сохранены.(ID:" . $amodel->id . " )";
                    }
                }

                $icount++;
            }
        }

        // Обновление словаря RkDic

        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 1')->one();

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
        if (empty($cmdguid)) {
            $cmdguid = 'пусто';
        }
        if (empty($posid)) {
            $posid   = 'пусто';
        }
        if (empty($array)) {
            $array   = array(0 => '0');
        }

        if (empty($er)) {
            $er  = 'пусто';
        }
        if (empty($er3)) {
            $er3 = 'пусто';
        }
        if (empty($er2)) {
            $er2 = 'пусто';
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
        $this->log(print_r($er, true));
        $this->log(print_r($er2, true));
        $this->log(print_r($er3, true));
        $this->log('============EVENT END======================');
    }

}
