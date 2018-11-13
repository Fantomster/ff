<?php

namespace frontend\modules\clientintegr\modules\rkws\components;

use yii;
use api\common\models\RkAccess;
use api\common\models\RkSession;
use frontend\modules\clientintegr\modules\rkws\components\UUID;
use common\models\User;
use api\common\models\RkTasks;
use api\common\models\RkStore;
use api\common\models\RkStoretree;
use creocoder\nestedsets\NestedSetsBehavior;
use api\common\models\RkDic;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StoreHelper extends AuthHelper
{

    //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/store";
    /**
     * Отправка запроса
     *
     * @return bool
     */
    public function getStore()
    {
        $isLog = new DebugHelper();
        $isLog->setLogFile('../runtime/logs/rk_request_store_' . date("Y-m-d_H-i-s") . '.log');

        if (!$this->Authorizer()) {
            $isLog->logAppendString('Can\'t perform authorization ');
            exit();
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_stores" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/store' . '">
    <PARAM name="object_id" val="' . $this->restr->code . '" />
    </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

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
            $isLog->logAppendString('Error: ' . print_r($tmodel->getFirstErrors(), true));
        }

        // Обновление словаря RkDic
        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 2')->one();

        if (!$rmodel) {
            $isLog->logAppendString('RKDIC TMODEL NOT FOUND. Nothing has been saved.');
        } else {
            $rmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 2;
            $rmodel->obj_count = 0;
            if (!$rmodel->save()) {
                $isLog->logAppendString('Error: ' . print_r($rmodel->getFirstErrors(), true));
            } else {
                $isLog->logAppendString('Данные справочника DIC успешно сохранены.');
            }
        }

        $isLog->logAppendString('Response API: ' . print_r($res, true));

        return true;
    }

    /**
     * callback
     */
    public function callback()
    {

        ini_set('MAX_EXECUTION_TIME', -1);
        $getr = Yii::$app->request->getRawBody();
        $myXML = simplexml_load_string($getr);
        $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid']; // Try to find guid in cmdguid or taskguid
        $posid = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';

        $isLog = new DebugHelper();
        $isLog->setLogFile('../runtime/logs/rk_callback_store_' . date("Y-m-d_H-i-s") . '_' . $cmdguid . '.log');

        try {
            $array = [];

            if (!$cmdguid) {
                $cmdguid = 'noGUID';
            }

            $isLog->logAppendString('=========================================');
            $isLog->logAppendString(date("Y-m-d H:i:s") . ' : Store callback received... ');
            $isLog->logAppendString('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
            $isLog->logAppendString('=========================================');
            $isLog->logAppendString(substr($getr, 0, 600));

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
                    $isLog->logAppendString("Cannot save task (" . $cmdguid . ") with error: (" . $array['code'] . ")");
                    exit;
                } else {
                    $isLog->logAppendString('Task with external ERROR with guid ' . $cmdguid . 'successfully saved!');
                    $isLog->logAppendString("Task with guid (" . $cmdguid . ") with error: (" . $array['code'] . ") successfully saved.");
                    exit;
                }
            }

            // We got no errors. Try to parse XML with no external errors
            $icount = 0;
            $gcount = 0;

            RkStoretree::updateAll(['active' => 0], ['acc' => $acc]);

            foreach ($this->iterator($myXML->STOREGROUP) as $storegroup) {
                $gcount++;

                foreach ($storegroup->attributes() as $c => $d) {
                    if ($c == 'rid') $arr[$gcount]['rid'] = strval($d[0]);
                    if ($c == 'name') $arr[$gcount]['name'] = strval($d[0]);
                    if ($c == 'parent') $arr[$gcount]['parent'] = strval($d[0]);
                }

                $arr[$gcount]['type'] = 1;
                $iparent = $gcount;
                $ridarray[$arr[$gcount]['rid']] = $gcount;
                $spar = $arr[$gcount]['rid'];

                if ($arr[$gcount]['parent'] === '') {
                    $rtree = new RkStoretree(['name' => $arr[$gcount]['name']]);
                    $rtree->rid = 0;
                    $rtree->prnt = 0;
                    $rtree->disabled = 1;
                    $rtree->acc = $acc;
                    $rtree->makeRoot();
                } else {
                    ${'rid' . $arr[$gcount]['rid']} = new RkStoretree(['name' => $arr[$gcount]['name']]);
                    ${'rid' . $arr[$gcount]['rid']}->type = 1;
                    ${'rid' . $arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                    ${'rid' . $arr[$gcount]['rid']}->prnt = $arr[$gcount]['parent'];
                    ${'rid' . $arr[$gcount]['rid']}->disabled = 1;
                    ${'rid' . $arr[$gcount]['rid']}->acc = $acc;

                    if ($arr[$gcount]['parent'] === '0') {
                        ${'rid' . $arr[$gcount]['rid']}->prependTo($rtree);
                    } else {
                        ${'rid' . $arr[$gcount]['rid']}->prependTo(${'rid' . $arr[$gcount]['parent']});
                    }
                    $icount++;
                }

                foreach ($this->iterator($storegroup->STORE) as $store) {
                    $gcount++;
                    foreach ($store->attributes() as $a => $b) {
                        $arr[$gcount][$a] = strval($b[0]);
                    }

                    $arr[$gcount]['type'] = 2;
                    $arr[$gcount]['parent'] = $iparent;

                    ${'srid' . $arr[$gcount]['rid']} = new RkStoretree(['name' => $arr[$gcount]['name']]);
                    ${'srid' . $arr[$gcount]['rid']}->type = 2;
                    ${'srid' . $arr[$gcount]['rid']}->prnt = $spar;
                    ${'srid' . $arr[$gcount]['rid']}->rid = $arr[$gcount]['rid'];
                    ${'srid' . $arr[$gcount]['rid']}->disabled = 0;
                    ${'srid' . $arr[$gcount]['rid']}->acc = $acc;

                    if ($spar === '0' || $spar === '') {
                        ${'srid' . $arr[$gcount]['rid']}->appendTo($rtree);
                    } else {
                        ${'srid' . $arr[$gcount]['rid']}->appendTo(${'rid' . $spar});
                    }
                    $icount++;
                }
            }

            // Update task after XML
            if (!$tmodel->setCallbackXML()) {
                $isLog->logAppendString('ERROR:: Task after XML parsing cannot be saved!!');
                exit;
            } else {
                $isLog->logAppendString('SUCCESS:: Task after XML successfully saved!');
            }
            $isLog->logAppendString('SUCCESS:: Stories saved');

            $tmodel->rcount = $icount;
            $tmodel->intstatus_id = RkTasks::INTSTATUS_DICOK;

            // Обновление словаря RkDic
            $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $acc])->andWhere('dictype_id = 2')->one();

            if (!$rmodel) {
                $isLog->logAppendString('ERROR:: Dictionary to update stories is not found.');
                exit;
            }

            $fcount = RkStoretree::find()
                ->where('acc= :org_id', [':org_id' => $acc])
                ->andWhere('active = 1 and type = 2')
                ->count();

            $rmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 6;
            $rmodel->obj_count = isset($fcount) ? $fcount : 0;

            if (!$rmodel->save()) {
                $isLog->logAppendString('ERROR:: Dictionary ' . $rmodel->id . 'cannot be saved - ' . print_r($rmodel->getFirstErrors(), 1));
                exit;
            } else {
                $isLog->logAppendString('SUCCESS:: Dictionary ' . $rmodel->id . ' is successfully saved.');
            }

            $tmodel->intstatus_id = RkTasks::INTSTATUS_FULLOK;

            if (!$tmodel->setCallbackEnd()) {
                $isLog->logAppendString('ERROR:: Task status THE END cannot be saved!!');
            } else {
                $isLog->logAppendString('SUCCESS:: All operations status is looking good');
            }
        } catch (\Throwable $e) {
            $isLog->logAppendString("!!! CATCH ERROR: " . $e->getMessage());
        }
    }

    /**
     * @param $items
     * @return \Generator
     */
    private function iterator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}
