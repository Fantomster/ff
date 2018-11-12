<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.07.2018
 * Time: 13:08
 */

namespace frontend\modules\clientintegr\modules\merc\helpers\api;

use api\common\models\merc\mercLog;
use common\models\AllServiceOperation;
use common\models\Journal;
use yii\base\Component;

class mercLogger extends Component
{
    const service_id = 4;

    static $opertionsList = [
        'getVetDocumentList' => ['code' => '8', 'comment' => 'получение всех ВСД предприятия'],
        'getVetDocumentChangeList' => ['code' => '7', 'comment' => 'получение ВСД (получение истории изменений)'],
        'getVetDocumentByUUID' => ['code' => '6', 'comment' => 'получение ВСД по его идентификатору'],
        'getVetDocumentDone' => ['code' => '3', 'comment' => 'оформление входящей партии'],
        'getStockEntryList' => ['code' => '12', 'comment' => 'получение актуального списка записей журнала'],
        'getStockEntryVersionList' => ['code' => '11', 'comment' => 'получение всех версий записи складского журнала по ее идентификатору'],
        'getStockEntryChangesList' => ['code' => '13', 'comment' => 'получение списка версий записей журнала (получение истории изменений)'],
        'getStockEntryByGuid' => ['code' => '10', 'comment' => 'получение последней (актуальной) версии записи складского журнала по ее идентификатору'],
        'getStockEntryByUuid' => ['code' => '9', 'comment' => 'получение конкретной версии записи складского журнала по ее идентификатору'],
        'resolveDiscrepancyOperation' => ['code' => '4', 'comment' => 'оформление результатов инвентаризации'],
        'prepareOutgoingConsignmentOperation' => ['code' => '2', 'comment' => 'оформление транспортной партии'],
        'registerProductionOperation' => ['code' => '1', 'comment' => 'оформление производственной партии'],
    ];

    //private $service;
    protected static $_instance;

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
            //self::$_instance->service = AllService::findOne(self::service_id);
        }
        return self::$_instance;
    }

    public function addMercLog($response, $method, $localTransactionId, $request_xml, $response_xml, $org_id = null)
    {
        $operation = $this->getServiceOperation($method);
        $journal = new Journal();
        $journal->service_id = self::service_id;
        $journal->operation_code = $operation->code."";
        if (\Yii::$app instanceof \Yii\web\Application) {
            $journal->user_id = \Yii::$app->user->id;
            $journal->organization_id = (\Yii::$app->user->identity)->organization_id;
        }
        else {
            $journal->organization_id = $org_id;
        }
        $journal->log_guide = $localTransactionId;
        $journal->type = ($response->application->status == 'COMPLETED') ? 'success' : 'error';
        $journal->response = ($journal->type == 'success') ? 'COMPLETE' :  serialize($response);

        $journal->save();


        $this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml, $org_id);

        if (\Yii::$app instanceof \Yii\web\Application) {
            if ($journal->type == mercLog::REJECTED) {
                throw new \Exception($journal->id, 600);
            }
        }
    }

    public function addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml, $org_id = null)
    {
        //Пишем лог
        $log = new mercLog();
        $log->applicationId = $response->application->applicationId;
        $log->status = $response->application->status;
        $log->action = $method;
        $log->localTransactionId = $localTransactionId;
        $log->request = $request_xml;
        $log->response = $response_xml;
        $log->organization_id = $org_id;

        if ($log->status == mercLog::REJECTED) {
            $log->description = json_encode($response->application->errors, JSON_UNESCAPED_UNICODE);
        }

        $log->save();
        //var_dump($log->getErrors());

        if ($log->status == mercLog::REJECTED) {
            throw new \Exception($log->id, 600);
        }
    }


    private function getServiceOperation($denom)
    {
        $operation = AllServiceOperation::findOne(['service_id' => self::service_id, 'denom' => $denom]);

        if($operation != null)
            return $operation;

        $operation = new AllServiceOperation();
        $operation->service_id = self::service_id;
        $operation->denom = $denom;
        $operation->code = self::$opertionsList[$denom]['code'];
        $operation->comment = self::$opertionsList[$denom]['comment'];

        $operation->save();
        return $operation;
    }

    public function addMercLogDict ($result, $localTransactionId, $response, $org_id = null)
    {
        $response = mb_strimwidth($response, 0,32000);
        $operation = $this->getServiceOperation($localTransactionId);
        $journal = new Journal();
        $journal->service_id = self::service_id;
        $journal->operation_code = $operation->code."";
        $journal->log_guide = $localTransactionId;
        $journal->type = ($result == 'COMPLETE') ? 'success' : 'error';
        $journal->response = ($journal->type == 'success') ? 'COMPLETE' :  $response;
        $journal->organization_id = $org_id;

        $journal->save();

        $journal->getErrors();

        //$this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml);

    }

    /*public function addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml)
    {
        //Пишем лог
        $log = new mercLog();
        $log->applicationId = $response->application->applicationId;
        $log->status = $response->application->status;
        $log->action = $method;
        $log->localTransactionId = $localTransactionId;
        $log->request = $request_xml;
        $log->response = $response_xml;

        if ($log->status == mercLog::REJECTED) {
            $log->description = json_encode($response->application->errors, JSON_UNESCAPED_UNICODE);
        }

        $log->save();
        //var_dump($log->getErrors());

        /*if ($log->status == mercLog::REJECTED) {
            throw new \Exception($log->id, 600);
        }
    }*/

}