<?php

/**
 * Class NoAuthWebApi
 *
 * @package   api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-04
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace api_web\classes;

use api_web\components\Registry;
use api_web\modules\integration\classes\sync\ServiceRkws;
use common\models\Journal;
use common\models\Waybill;
use Yii;
use api_web\modules\integration\classes\sync\AbstractSyncFactory;
use api_web\modules\integration\classes\SyncLog;
use api_web\modules\integration\classes\SyncServiceFactory;
use yii\web\BadRequestHttpException;
use common\models\AllServiceOperation;
use common\models\OuterTask;

class NoAuthWebApi
{
    public function loadDictionary(OuterTask $task)
    {
        # 2.1.1. Trace callback operation with task_id
        SyncLog::trace('Callback operation `task_id` params is ' . $task->id);

        # 2.1.2. Check oper_code
        $oper = AllServiceOperation::findOne($task->oper_code);
        if (!$oper) {
            SyncLog::trace('Operation code (' . $task->oper_code . ') is wrong!');
            throw new BadRequestHttpException("wrong_param|" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        }
        $allOpers = AbstractSyncFactory::getAllSyncOperations();

        SyncLog::trace('Try to receive XML data...');
        if (array_key_exists($oper->denom, $allOpers) && isset($allOpers[$oper->denom])) {
            $entityName = $allOpers[$oper->denom];
            $entity = new $entityName(SyncServiceFactory::ALL_SERVICE_MAP[$oper->service_id], $oper->service_id);
            /** @var $entity AbstractSyncFactory */
            if (method_exists($entity, 'receiveXmlData')) {
                $body = Yii::$app->request->getRawBody();
                if (empty($body)) {
                    SyncLog::trace('Empty response: ' . $task->id);
                    return 'false';
                }
                SyncLog::trace($body);

                if ($entity instanceof ServiceRkws) {
                    $entity->orgId = $task->org_id;
                }

                $res = $entity->receiveXmlData($task, $body);
                SyncLog::trace($res);
                return $res;
            }
        }
        SyncLog::trace('Fail!');
        return 'false';
    }

    public function sendWaybill(OuterTask $task)
    {

        # 2.1.1. Trace callback operation with task_id
        SyncLog::trace('Callback operation `task_id` params is ' . $task->id);

        # 2.1.2. Check oper_code
        $oper = AllServiceOperation::findOne($task->oper_code);
        if (!$oper) {
            SyncLog::trace('Operation code (' . $task->oper_code . ') is wrong!');
            throw new BadRequestHttpException("wrong_param|" . AbstractSyncFactory::CALLBACK_TASK_IDENTIFIER);
        }

        $allOpers = AbstractSyncFactory::getAllSyncOperations();

        SyncLog::trace('Try to receive XML data...');
        if (array_key_exists($oper->denom, $allOpers) && isset($allOpers[$oper->denom])) {
            $entityName = $allOpers[$oper->denom];
            $entity = new $entityName(SyncServiceFactory::ALL_SERVICE_MAP[$oper->service_id], $oper->service_id);
            /** @var AbstractSyncFactory $entity */
            if (method_exists($entity, 'receiveXmlData')) {
                $res = $entity->receiveXMLDataWaybill($task, Yii::$app->request->getRawBody());
                $xml = (array)simplexml_load_string($res);
                $waybill = Waybill::findOne($task->waybill_id);
                $journal = new Journal();
                $journal->service_id = $waybill->service_id;
                $journal->operation_code = 21;
                $journal->log_guide = 'any_call';
                $journal->organization_id = $waybill->acquirer_id;
                // Когда все хорошо и накладная создалась в R-keeper
                if (array_key_exists('DOC', $xml)) {
                    $waybill->status_id = Registry::WAYBILL_UNLOADED;
                    $journal->type = 'success';
                    $journal->response = $waybill->id;
                } elseif (array_key_exists('ERROR', $xml)) {
                    //Когда случилась ошибка
                    $error = (array)$xml['ERROR'];
                    $waybill->status_id = Registry::WAYBILL_ERROR;
                    $journal->type = 'error';
                    $journal->response = $error['@attributes']['Text'];
                }
                $waybill->save();
                $journal->save();
                SyncLog::trace($res);
                return $res;
            }
        }
        SyncLog::trace('Fail!');
        return 'false';
    }
}