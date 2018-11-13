<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.07.2018
 * Time: 13:08
 */

namespace api_web\modules\integration\modules\vetis\api;

use api\common\models\merc\mercLog;
use common\models\AllServiceOperation;
use common\models\Journal;
use yii\base\Component;

/**
 * Class mercLogger
 *
 * @package api_web\modules\integration\modules\vetis\api
 */
class mercLogger extends Component
{
    /**
     *
     */
    const service_id = 4;

    /**
     * @var array
     */
    static $opertionsList = [
        'getVetDocumentList'                  => ['code' => '8', 'comment' => 'получение всех ВСД предприятия'],
        'getVetDocumentChangeList'            => ['code' => '7', 'comment' => 'получение ВСД (получение истории изменений)'],
        'getVetDocumentByUUID'                => ['code' => '6', 'comment' => 'получение ВСД по его идентификатору'],
        'getVetDocumentDone'                  => ['code' => '3', 'comment' => 'оформление входящей партии'],
        'getStockEntryList'                   => ['code' => '12', 'comment' => 'получение актуального списка записей журнала'],
        'getStockEntryVersionList'            => ['code' => '11', 'comment' => 'получение всех версий записи складского журнала по ее идентификатору'],
        'getStockEntryChangesList'            => ['code' => '13', 'comment' => 'получение списка версий записей журнала (получение истории изменений)'],
        'getStockEntryByGuid'                 => ['code' => '10', 'comment' => 'получение последней (актуальной) версии записи складского журнала по ее идентификатору'],
        'getStockEntryByUuid'                 => ['code' => '9', 'comment' => 'получение конкретной версии записи складского журнала по ее идентификатору'],
        'resolveDiscrepancyOperation'         => ['code' => '4', 'comment' => 'оформление результатов инвентаризации'],
        'prepareOutgoingConsignmentOperation' => ['code' => '2', 'comment' => 'оформление транспортной партии'],
        'registerProductionOperation'         => ['code' => '1', 'comment' => 'оформление производственной партии'],
    ];

    //private $service;
    /**
     * @var
     */
    protected static $_instance;

    /**
     * @return mercLogger
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
            //self::$_instance->service = AllService::findOne(self::service_id);
        }
        return self::$_instance;
    }

    /**
     * @param      $response
     * @param      $method
     * @param      $localTransactionId
     * @param      $request_xml
     * @param      $response_xml
     * @param null $org_id
     * @throws \Exception
     */
    public function addMercLog($response, $method, $localTransactionId, $request_xml, $response_xml, $org_id = null)
    {
        $operation = $this->getServiceOperation($method);
        $journal = new Journal();
        $journal->service_id = self::service_id;
        $journal->operation_code = $operation->code . "";
        if (\Yii::$app instanceof \Yii\web\Application) {
            $journal->user_id = \Yii::$app->user->id;
            $journal->organization_id = (\Yii::$app->user->identity)->organization_id;
        } else {
            $journal->organization_id = $org_id;
        }
        $journal->log_guide = $localTransactionId;
        $journal->type = ($response->application->status == 'COMPLETED') ? 'success' : 'error';
        $journal->response = ($journal->type == 'success') ? 'COMPLETE' : serialize($response);

        $journal->save();

        $this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml, $org_id = null);

        if (\Yii::$app instanceof \Yii\web\Application) {
            if ($journal->type == mercLog::REJECTED) {
                throw new \Exception($journal->id, 600);
            }
        }
    }

    /**
     * @param      $response
     * @param      $method
     * @param      $localTransactionId
     * @param      $request_xml
     * @param      $response_xml
     * @param null $org_id
     * @throws \Exception
     */
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

        if ($log->status == mercLog::REJECTED) {
            throw new \Exception($log->id, 600);
        }
    }

    /**
     * @param $denom
     * @return AllServiceOperation|null
     */
    private function getServiceOperation($denom)
    {
        $operation = AllServiceOperation::findOne(['service_id' => self::service_id, 'denom' => $denom]);

        if ($operation != null) {
            return $operation;
        }
        $operation = new AllServiceOperation();
        $operation->service_id = self::service_id;
        $operation->denom = $denom;
        $operation->code = self::$opertionsList[$denom]['code'];
        $operation->comment = self::$opertionsList[$denom]['comment'];

        $operation->save();
        return $operation;
    }

    /**
     * @param $result
     * @param $localTransactionId
     * @param $response
     */
    public function addMercLogDict($result, $localTransactionId, $response)
    {
        $operation = $this->getServiceOperation($localTransactionId);
        $journal = new Journal();
        $journal->service_id = self::service_id;
        $journal->operation_code = $operation->code . "";
        $journal->log_guide = $localTransactionId;
        $journal->type = $result;
        $journal->response = ($journal->type == 'COMPLETE') ? 'COMPLETE' : $response;

        $journal->save();

        $journal->getErrors();

        //$this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml);

    }
}