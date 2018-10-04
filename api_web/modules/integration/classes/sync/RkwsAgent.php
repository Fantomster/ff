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
use api\common\models\RkAgent;
use api_web\modules\integration\classes\SyncLog;
use common\models\OuterTask;
use yii\web\BadRequestHttpException;

class RkwsAgent extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'agent';

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_corrs';

    public function receiveXmlData(OuterTask $task, string $data = null)
    {

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

        $saveResult = true;
        $saveCounts = 0;
        $err = [];
        if ($array && $cmdguid) {
            foreach ($array as $a) {
                $agent = RkAgent::findOne(['acc' => $task->org_id, 'rid' => $a['rid']]);
                $ts = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                if (!$agent) {
                    $agent = new RkAgent();
                    $agent->created_at = $ts;
                } else {
                    $agent->updated_at = $ts;
                }
                $agent->acc = $task->org_id;
                $agent->rid = $a['rid'];
                $agent->denom = $a['name'];
                $agent->agent_type = $a['type'];
                if ($agent->save()) {
                    $task->callbacked_at = $ts;
                    $task->int_status_id = OuterTask::STATUS_CALLBACKED;
                    $task->retry++;
                    $saveCounts++;
                } else {
                    $err['agent'][$agent->id][] = $agent->errors;
                    $saveResult = false;
                }
            }
            if (!$task->save()) {
                $err['task'][] = $task->errors;
                $saveResult = false;
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
