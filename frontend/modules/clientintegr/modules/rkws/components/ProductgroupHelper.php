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

    public $result      = [];
    public $logCategory = "rkws_log";

    //  const CALLBACK_URL = "https://api.f-keeper.ru/api/web/v1/restor/callback/store";

    public function getCategory()
    {
        if (!$this->Authorizer()) {
            $this->log('Can\'t perform authorization ');
            exit();
        }

        $guid = UUID::uuid4();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_goodgroups" tasktype="any_call" guid="' . $guid . '" callback="' . Yii::$app->params['rkeepCallBackURL'] . '/productgroup' . '" timeout="3600">
    <PARAM name="object_id" val="' . $this->restr->code . '" />    
    <PARAM name="include_goods" val="0" />
    </RQ>';

        $res = ApiHelper::sendCurl($xml, $this->restr);

        $tmodel               = new RkTasks();
        $tmodel->tasktype_id  = 11;
        $tmodel->acc          = $this->org;
        $tmodel->fid          = 1;
        $tmodel->guid         = $res['respcode']['taskguid'];
        $tmodel->fcode        = $res['respcode']['code'];
        $tmodel->version      = $res['respcode']['version'];
        $tmodel->isactive     = 1;
        $tmodel->created_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $tmodel->intstatus_id = 1;

        if (!$tmodel->save()) {
            $this->log('Error: ' . print_r($tmodel->getFirstErrors(), true));
        }

        // Обновление словаря RkDic
        $rmodel = RkDic::find()->andWhere('org_id= :org_id', [':org_id' => $this->org])->andWhere('dictype_id = 5')->one();

        if (!$rmodel) {
            $this->log('RKDIC TMODEL NOT FOUND. Nothing has been saved.');
        } else {
            $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 2;
            $rmodel->obj_count    = 0;
            if (!$rmodel->save()) {
                $this->log('Error: ' . print_r($rmodel->getFirstErrors(), true));
            } else {
                $this->log('Данные справочника DIC успешно сохранены.');
            }
        }

        $this->log('Response API: ' . print_r($res, true));
        return true;
    }

    public function callback()
    {
        ini_set('MAX_EXECUTION_TIME', -1);
        $getr    = Yii::$app->request->getRawBody();
        $myXML   = simplexml_load_string($getr);
        $cmdguid = $myXML['cmdguid'] ? $myXML['cmdguid'] : $myXML['taskguid']; // Try to find guid in cmdguid or taskguid
        $posid   = $myXML['posid'] ? $myXML['posid'] : '-нет POSID-';

        $array = [];
        try {

            if (!$cmdguid) {
                $cmdguid = 'noGUID';
            }

            $this->log('=========================================');
            $this->log(date("Y-m-d H:i:s") . ' : Store callback received... ');
            $this->log('CMDGUID: ' . $cmdguid . ' || POSID: ' . $posid);
            $this->log('=========================================');
            $this->log(print_r($getr, 1));

            // Checking if the Task is active
            $tmodel = RkTasks::find()->where('guid= :guid', [':guid' => $cmdguid])->one();

            if (!$tmodel) {
                $this->log('ERROR:: Task with guid ' . $cmdguid . 'has not been found!!');
                echo "Не найдена задача с id: (" . $cmdguid . ")";
                exit;
            } else {
                $this->log('-- Task with guid ' . $cmdguid . ' has been found.');
            }

            $acc              = $tmodel->acc;
            $tmodel->isactive = 0;
            $this->log('-- after setCallbackStart()!!!');
            $tmodel->setCallbackStart();
            // Parsing XML for errors
            $this->log('-- check XML ERROR!');
            foreach ($myXML->ERROR as $err) {
                foreach ($err->attributes() as $e => $h) {
                    if ($e == 'code')
                        $array['code'] = strval($h[0]);
                    if ($e == 'text')
                        $array['text'] = strval($h[0]);
                }
            }

            $this->log('-- check error code!');
            if (isset($array['code'])) {
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
            $this->log('-- UPDATE rk_category SET active=0');
            RkCategory::updateAll(['active' => 0], ['acc' => $acc]);

            /**
             * Обработка данных
             * */
            $gcount = $this->handleFile($myXML, $acc);

            $this->log('-- after setCallbackXML!');
            if (!$tmodel->setCallbackXML()) {
                $this->log('ERROR:: Task after XML parsing cannot be saved!!');
                exit;
            } else {
                $this->log('SUCCESS:: Task after XML successfully saved!');
            }

            $this->log('SUCCESS:: Categories saved');

            $tmodel->rcount       = $gcount;
            $tmodel->intstatus_id = RkTasks::INTSTATUS_DICOK;

            // Обновление словаря RkDic
            $rmodel = RkDic::find()->where('dictype_id = 5 AND org_id= :org_id', [':org_id' => $acc])->one();

            if (!$rmodel) {
                $this->log('ERROR:: Dictionary to update categories is not found.');
                exit;
            }

            $fcount = RkCategory::find()
                    ->where('active = 1 AND acc= :org_id', [':org_id' => $acc])
                    ->count('*');

            $rmodel->updated_at   = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            $rmodel->dicstatus_id = 6;
            $rmodel->obj_count    = $fcount ?? 0;

            if (!$rmodel->save()) {
                $er3 = $rmodel->getErrors();
                $this->log('ERROR:: Dictionary ' . $rmodel->id . 'cannot be saved - ' . print_r($er3, true));
                exit;
            } else {
                $this->log('SUCCESS:: Dictionary ' . $rmodel->id . ' is successfully saved.');
            }

            $tmodel->intstatus_id = RkTasks::INTSTATUS_FULLOK;

            if (!$tmodel->setCallbackEnd()) {
                $this->log('ERROR:: Task status THE END cannot be saved!!');
            } else {
                $this->log('SUCCESS:: All operations status is looking good');
                echo 'SUCCESS:: All operations status is looking good';
            }
        } catch (\Throwable $e) {
            $this->log("!!! CATCH ERROR: " . $e->getMessage());
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
     * @return int
     */
    public function handleFile(\SimpleXMLElement $myXML, $acc)
    {
        $count = 0;
        //Раскладываем XML в массив
        $this->log("-- parsing XML start: [" . date("d.m.Y H:i:s") . "]");

        foreach ($this->iterator($myXML->ITEM) as $item) {
            $rid                = (string) $item->attributes()->rid;
            $this->result[$rid] = [
                'rid'    => $rid,
                'name'   => (string) $item->attributes()->name,
                'parent' => trim((string) $item->attributes()->parent)
            ];
            $count++;
        }

        $this->log("-- parsing XML   end: [" . date("d.m.Y H:i:s") . "]");
        $this->log("-- found {$count}!");
        $this->log("-- create items! start: [" . date("d.m.Y H:i:s") . "]");

        foreach ($this->iterator($this->result) as $item) {
            $this->createItem($item, $acc);
        }

        $this->log("-- create items!   end: [" . date("d.m.Y H:i:s") . "]");

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
