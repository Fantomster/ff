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

class ProductgroupHelper extends AuthHelper
{
    public $result = [];

    //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/store";

    public function getCategory()
    {
        $isLog = new DebugHelper();
        $isLog->setLogFile('../runtime/logs/rk_request_prodgroup_' . date("Y-m-d_H-i-s") . '.log');
        if (!$this->Authorizer()) {
            $isLog->logAppendString('Can\'t perform authorization ');
            exit();
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goodgroups" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/productgroup' . '" timeout="3600">
    <PARAM name="object_id" val="' . $this->restr->code . '" />    
    <PARAM name="include_goods" val="0" />
    </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);
        $isLog = new DebugHelper();



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
            $isLog->logAppendString('Error: ' . print_r($tmodel->getFirstErrors(), true));
        }

        // Обновление словаря RkDic
        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 5')->one();

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

    public function callback()
    {
        ini_set('MAX_EXECUTION_TIME', -1);
        $getr = Yii::$app->request->getRawBody();
        $myXML = simplexml_load_string($getr);
        $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid']; // Try to find guid in cmdguid or taskguid
        $posid = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';
        $isLog = new DebugHelper();
        $isLog->setLogFile('../runtime/logs/rk_callback_pgroup_' . date("Y-m-d_H-i-s") . '_' . $cmdguid . '.log');

        $array = [];
        try {

            if (!$cmdguid) {
                $cmdguid = 'noGUID';
            }

            $isLog->logAppendString('=========================================');
            $isLog->logAppendString(date("Y-m-d H:i:s") . ' : Store callback received... ');
            $isLog->logAppendString('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
            $isLog->logAppendString('=========================================');
            $isLog->logAppendString(print_r($getr, 1));

            // Checking if the Task is active
            $tmodel = RkTasks::find()->where('guid= :guid', [':guid' => $cmdguid])->one();

            if (!$tmodel) {
                $isLog->logAppendString('ERROR:: Task with guid ' . $cmdguid . 'has not been found!!');
                echo "Не найдена задача с id: (" . $cmdguid . ")";
                exit;
            } else {
                $isLog->logAppendString('-- Task with guid ' . $cmdguid . ' has been found.');
            }

            $acc = $tmodel->acc;
            $tmodel->isactive = 0;
            $isLog->logAppendString('-- after setCallbackStart()!!!');
            $tmodel->setCallbackStart();
            // Parsing XML for errors
            $isLog->logAppendString('-- check XML ERROR!');
            foreach ($myXML->ERROR as $err) {
                foreach ($err->attributes() as $e => $h) {
                    if ($e == 'code') $array['code'] = strval($h[0]);
                    if ($e == 'text') $array['text'] = strval($h[0]);
                }
            }

            $isLog->logAppendString('-- check error code!');
            if (isset($array['code'])) {
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
            $isLog->logAppendString('-- UPDATE rk_category SET active=0');
            RkCategory::updateAll(['active' => 0], ['acc' => $acc]);

            /**
             * Обработка данных
             **/
            $gcount = $this->handleFile($myXML, $acc, $isLog);

            $isLog->logAppendString('-- after setCallbackXML!');
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
            $rmodel = RkDic::find()->where('dictype_id = 5 AND org_id= :org_id', [':org_id' => $acc])->one();

            if (!$rmodel) {
                $isLog->logAppendString('ERROR:: Dictionary to update categories is not found.');
                exit;
            }

            $fcount = RkCategory::find()
                ->where('active = 1 AND acc= :org_id', [':org_id' => $acc])
                ->count('*');

            $rmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 6;
            $rmodel->obj_count = $fcount ?? 0;

            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
                $isLog->logAppendString('ERROR:: Dictionary ' . $rmodel->id . 'cannot be saved - ' . print_r($er3, true));
                exit;
            } else {
                $isLog->logAppendString('SUCCESS:: Dictionary ' . $rmodel->id . ' is successfully saved.');
            }

            $tmodel->intstatus_id = RkTasks::INTSTATUS_FULLOK;

            if (!$tmodel->setCallbackEnd()) {
                $isLog->logAppendString('ERROR:: Task status THE END cannot be saved!!');
            } else {
                $isLog->logAppendString('SUCCESS:: All operations status is looking good');
                echo 'SUCCESS:: All operations status is looking good';
            }
        } catch (\Throwable $e) {
            $isLog->logAppendString("!!! CATCH ERROR: " . $e->getMessage());
        }
    }

    /**
     * @param $item
     * @param $acc
     * @return RkCategory|null|static
     */
    private function createItem($item, $acc)
    {
        $parent = empty($item['parent']) ? 0 : $item['parent'];

        $model = RkCategory::findOne([
            'acc'  => $acc,
            'rid'  => $item['rid'],
            'prnt' => $parent
        ]);

        if (empty($model)) {
            $model = new RkCategory([
                'name' => $item['name'],
                'rid'  => $item['rid'],
                'prnt' => $parent,
                'acc'  => $acc
            ]);

            if ($parent == 0) {
                $model->makeRoot();
            } else {
                $parentModel = RkCategory::findOne([
                    'acc' => $acc,
                    'rid' => $parent
                ]);

                if (empty($parentModel)) {
                    $this->createItem($this->result[$parent], $acc);
                } else {
                    //Тип для подкатегорий
                    $model->type = 1;
                    $model->prependTo($parentModel);
                }
            }
        } else {
            //Если изменили имя
            if ($model->name != $item['name']) {
                $model->name = $item['name'];
            }
            //Тип для подкатегорий
            if ($parent !== 0) {
                $model->type = 1;
            }
            $model->active = 1;
            $model->save();
        }

        return $model;
    }

    /**
     * @param \SimpleXMLElement $myXML
     * @param                   $acc
     * @param DebugHelper       $isLog
     * @return int
     */
    public function handleFile(\SimpleXMLElement $myXML, $acc, DebugHelper $isLog)
    {
        $count = 0;
        //Раскладываем XML в массив
        $isLog->logAppendString("-- parsing XML start: [" . date("d.m.Y H:i:s") . "]");

        foreach ($this->iterator($myXML->ITEM) as $item) {
            $rid = (string)$item->attributes()->rid;
            $this->result[$rid] = [
                'rid'    => $rid,
                'name'   => (string)$item->attributes()->name,
                'parent' => trim((string)$item->attributes()->parent)
            ];
            $count++;
        }

        $isLog->logAppendString("-- parsing XML   end: [" . date("d.m.Y H:i:s") . "]");
        $isLog->logAppendString("-- found {$count}!");
        $isLog->logAppendString("-- create items! start: [" . date("d.m.Y H:i:s") . "]");

        foreach ($this->iterator($this->result) as $item) {
            $this->createItem($item, $acc);
        }

        $isLog->logAppendString("-- create items!   end: [" . date("d.m.Y H:i:s") . "]");

        return $count;
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
