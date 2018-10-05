<?php

/**
 * Class RkwsAgent
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use Yii;
use yii\web\BadRequestHttpException;
use common\models\OrganizationDictionary;
use common\models\OuterAgent;
use common\models\OuterDictionary;
use common\models\OuterTask;
use api_web\modules\integration\classes\SyncLog;

class RkwsAgent extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'agent';

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_corrs';

    public function receiveXMLData(OuterTask $task, string $data = null, string $entityName = null)
    {
        
        $saveResult = true;
        $saveCounts = 0;
            
        
        $outerDic = OuterDictionary::findOne(['service_id' => $task->service_id, 'name' => $this->index]);
        if (!$outerDic) {
            SyncLog::trace('OuterDictionary not found!');
            throw new BadRequestHttpException("outer_dic_not_found");
        }

        $orgDic = OrganizationDictionary::findOne(['outer_dic_id' => $outerDic->id,
            'org_id' => $task->org_id, 'status_id' => OrganizationDictionary::STATUS_DISABLED]);
        if ($orgDic) {
            SyncLog::trace('OrganizationDictionary not found! Create it!');
            throw new BadRequestHttpException("org_dic_disabled");
        } else {
            $orgDic = OrganizationDictionary::findOne(['outer_dic_id' => $outerDic->id, 'org_id' => $task->org_id]);
            if (!$orgDic) {
                $orgDic = new OrganizationDictionary(['outer_dic_id' => $outerDic->id,
                    'org_id' => $task->org_id, 'status_id' => OrganizationDictionary::STATUS_ACTIVE, 'count' => 0]);
            }
        }
        $err = [];
        if(!$orgDic->save()) {
            $err['org_dic'][] = $orgDic->errors;
            $saveResult = false;
        }

        $myXML = simplexml_load_string($data);
        SyncLog::trace('XML data: ' . $data . PHP_EOL . ' ---------------- ' . PHP_EOL);
        $gcount = 0;
        $array = [];

        foreach ($myXML->CORRGROUP as $corrgroup) {
            $grid = 0;
            $grname = 0;
            foreach ($corrgroup->attributes() as $k => $v) {
                if ($k == 'rid') $grid = strval($v[0]);
                if ($k == 'name') $grname = strval($v[0]);
            }
            foreach ($corrgroup->CORR as $corr) {
                $gcount++;
                $array[$gcount]['group_rid'] = $grid;
                $array[$gcount]['group_name'] = $grname;
                foreach ($corr->attributes() as $k => $v) {
                    $array[$gcount][$k] = strval($v[0]);
                }
            }
        }

        if (!$array) {
            SyncLog::trace('Wrong XML data!');
            throw new BadRequestHttpException("wrong_xml_data");
        }

        $cmdguid = strval($myXML['cmdguid']) ? strval($myXML['cmdguid']) : strval($myXML['taskguid']);

        $agentToDisable = OuterAgent::findAll(['org_id' => $task->org_id, 'service_id' => $task->service_id]);

        if ($array && $cmdguid) {
            foreach ($array as $a) {
                $agent = OuterAgent::findOne(['org_id' => $task->org_id, 'outer_uid' => $a['rid'],
                    'service_id' => $task->service_id]);
                if (!$agent) {
                    $agent = new OuterAgent();
                    $agent->org_id = $task->org_id;
                    $agent->outer_uid = $a['rid'];
                    $agent->service_id = $task->service_id;

                } elseif(array_key_exists($agent->id, $agentToDisable)) {
                    unset($agentToDisable[$agent->id]);
                }
                $agent->name = $a['name'];

                $agent->is_deleted = 0;
                if ($agent->save()) {
                    $task->int_status_id = OuterTask::STATUS_CALLBACKED;
                    $saveCounts++;
                } else {
                    $err['agent'][$agent->id][] = $agent->errors;
                    $saveResult = false;
                }
            }
            $task->retry++;
            if (!$task->save()) {
                $err['task'][] = $task->errors;
                $saveResult = false;
            }
            if ($saveCounts) {
                foreach($agentToDisable as $agent) {
                    $agent->is_deleted = 1;
                    if ($agent->save()) {
                        $saveCounts++;
                    } else {
                        $err['agent'][$agent->id][] = $agent->errors;
                        $saveResult = false;
                    }
                }
            }

        }

        if ($err) {
            SyncLog::trace('Save errors: '. json_encode($err));
        }

        if ($saveResult && $saveCounts) {
            return self::XML_LOAD_RESULT_SUCCESS;
        }
        return self::XML_LOAD_RESULT_FAULT;
    }
}
