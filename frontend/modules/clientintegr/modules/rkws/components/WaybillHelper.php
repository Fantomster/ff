<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use api\common\models\RkDicconst;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use api\common\models\RkTasks;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WaybillHelper extends AuthHelper
{

    // const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/waybill";

    public function sendWaybill($id)
    {
        if (!$this->Authorizer()) {
            echo "Can't perform authorization";
            return;
        }

        $guid = UUID::uuid4();

        $wmodel = \api\common\models\RkWaybill::findOne(['id' => $id]);

        $exportApproved = (RkDicconst::findOne(['denom' => 'useAcceptedDocs'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'useAcceptedDocs'])->getPconstValue() : 0;
        // $useAutoVAT            = (RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'useTaxVat'])->getPconstValue() : 1;
        // $exportVAT             = (RkDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() != null) ? RkDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() : 0;
        // $exportAutoNumber      = RkDicconst::findOne(['denom' => 'useAutoNumber'])->getPconstValue();

        $autoNumber = 'textcode="' . $wmodel->text_code . '" numcode="' . $wmodel->num_code . '" ';


        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/waybill' . '">
    <PARAM name="object_id" val="' . $this->restr->code . '" />
    <DOC date="' . Yii::$app->formatter->asDatetime($wmodel->doc_date, "php:Y-m-d") .
                '" corr="' . $wmodel->corr_rid .
                '" store="' . $wmodel->store->rid .
                '" active="' . $exportApproved . '"' .
                ' duedate="1" note="' . $wmodel->note .
                '" ' . $autoNumber . '>' . PHP_EOL;

        $recs = \api\common\models\RkWaybilldata::find()->select('rk_waybill_data.*, rk_product.rid as prid, rk_product.unit_rid')->leftJoin('rk_product', 'rk_product.id = product_rid')
                        ->andWhere('waybill_id = :wid', [':wid' => $id])
                        ->andWhere(['unload_status' => 1])
                        ->asArray(true)->all();

        // var_dump($recs);

        foreach ($recs as $rec) {
            // $xml .='<ITEM rid="'.$rec['prid'].'" quant="'.($rec["quant"]*1000).'" mu="'.$rec["munit_rid"].'" sum="'.($rec['sum']*100).'" vatrate="'.$rec['vat'].'" />'.PHP_EOL;
            $xml .= '<ITEM rid="' . $rec['prid'] . '" quant="' . ($rec["quant"] * 1000) . '" mu="' . $rec["unit_rid"] . '" sum="' . ($rec['sum'] * 100) . '" vatrate="' . ($rec['vat']) . '" />' . PHP_EOL;
        }

        $xml .= '</DOC>' . PHP_EOL .
                '</RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        $tmodel = new RkTasks();

        $tmodel->tasktype_id  = 33;
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

        // Обновление статуса выгрузки накладной

        $wmodel->status_id = 2;

        if (!$wmodel->save(false)) {
            echo "Не могу сохранить статус выгрузки накладной";
            exit;
        }

        return true;
    }

    public function callback()
    {
        $acc  = 0;
        $stat = 0;
        $getr   = Yii::$app->request->getRawBody();
        $myXML  = simplexml_load_string($getr);
        $gcount = 0;

        if (!isset($myXML->ERROR)) {
            $cmdguid = strval($myXML['cmdguid']);
            $posid   = strval($myXML['posid']);
            $stat    = 3;

            foreach ($myXML->DOC as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }
            }
        } else {
            $cmdguid = strval($myXML['taskguid']);
            $stat    = 4;
            foreach ($myXML->ERROR as $doc) {
                foreach ($doc->attributes() as $a => $b) {
                    $array[$gcount][$a] = strval($b[0]);
                }
            }
        }

        if (!empty($array) && !empty($cmdguid)) {
            // Заполнение tasks
            $tmodel = RkTasks::find()->andWhere('guid= :guid', [':guid' => $cmdguid])->one();

            if (!$tmodel) {
                $this->log('=======WAYBILL==EVENT==START=================');
                $this->log(date("Y-m-d H:i:s") . ':REQUEST:');
                $this->log('===========================================');
                $this->log('CMDGUID:' . $cmdguid);
                $this->log('*******************************************');
                $this->log(print_r($getr, true));
                $this->log('*******************************************');
                $this->log(print_r($array, true));
                $this->log('*******************************************');
                $this->log('TASK TMODEL NOT FOUND.!' . $cmdguid . '!');
                $this->log('Nothing has been saved.');
                exit;
            }

            $tmodel->intstatus_id = $stat;
            $tmodel->isactive     = 0;
            $tmodel->callback_at  = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            $acc = $tmodel->acc;

            if (!$tmodel->save()) {
                $er2 = $tmodel->getErrors();
            } else {
                $er2 = "Данные task успешно сохранены (ID:" . $tmodel->id . " )";
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
        if (empty($er2)) {
            $er2     = 'пусто';
        }

        $this->log('=======WAYBILL==EVENT==START=================');
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
        $this->log('============EVENT END======================');
    }

}
