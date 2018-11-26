<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkTasks;
use api\common\models\RkAgent;
use api\common\models\RkDic;
use api\common\models\RkService;
use DateTime;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ServiceHelper extends AuthHelper
{

    // const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/agent";

    public function getObjects()
    {
        if (!$this->Authorizer()) {
            echo "Can't perform authorization";
            return;
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="get_objects" guid="' . $guid . '">
    <PARAM name="onlyactive" val="0" />
     </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        //print "<pre>";
        //print_r ($res);
        //print "</pre>";
        //var_dump($res);

        yii::$app->db_api->// Set all records to deleted
                createCommand()->
                update('rk_service', ['is_deleted' => '1', 'status_id' => '0'])
                ->execute();

        // Обновление списка доступных объектов
        foreach ($res['resp'] as $obj) {
            $rcount = RkService::findone(['code' => $obj['code']]);

            $xml2 = '<?xml version="1.0" encoding="utf-8"?>
     <RQ cmd="get_objectinfo" guid="' . $guid . '">
     <PARAM name="object_id" val="' . $obj['code'] . '" />
     </RQ>';

            $res2 = ApiHelper::sendCurl($xml2, $this->restr);

            if (!$rcount) {
                $nmodel = new RkService();
                $nmodel->code       = $obj['code'] ? $obj['code'] : 0;
                $nmodel->name       = $obj['name'] ? $obj['name'] : 'Не задано';
                $nmodel->address    = isset($res2['resp']['address']) ? $res2['resp']['address'] : 'Не задано';
                $nmodel->phone      = isset($obj['phone']) ? $obj['phone'] : 'Не задано';
                $nmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $nmodel->is_deleted = 0;

                $nmodel->status_id = 0;

                $nmodel->td = new DateTime('2018-12-31');

                //var_dump($nmodel);

                if (!$nmodel->save()) {
                    echo "Can't save the service model";
                    exit;
                }
            } else {
                $statLic  = isset($obj['license_active']) ? $obj['license_active'] : '0';
                $modDate  = isset($obj['license_agent_expired_date']) ? new DateTime($obj['license_agent_expired_date']) : new DateTime('2001-01-01');
                $lastDate = isset($obj['agent_active_date']) ? new DateTime($obj['agent_active_date']) : new DateTime('2001-01-01');

                $rcount->is_deleted  = 0;
                $rcount->last_active = Yii::$app->formatter->asDate($lastDate, 'yyyy-MM-dd HH:mm:ss');
                $rcount->address     = isset($res2['resp']['address']) ? $res2['resp']['address'] : 'Не задано';
                $rcount->status_id   = $statLic;
                $rcount->td = new DateTime('2018-12-31');

                if (!$rcount->save()) {
                    echo "Can't save the service model";
                    exit;
                }
            }
        }

        return true;
    }

    public function callback()
    {
        $acc = 0;
        $getr   = Yii::$app->request->getRawBody();
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

        $cmdguid = strval($myXML['cmdguid']);
        $posid   = strval($myXML['posid']);

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
                    $amodel->acc        = $acc; // $tmodel->acc; 
                    $amodel->rid        = $a['rid'];
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

        if (empty($cmdguid)) {
            $cmdguid = 'пусто';
        }
        if (empty($posid)) {
            $posid   = 'пусто';
        }
        if (empty($array)) {
            $array   = array(0 => '0');
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
