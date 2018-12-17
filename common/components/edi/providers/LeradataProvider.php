<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\providers;

use common\components\edi\AbstractProvider;
use common\components\edi\AbstractRealization;
use common\components\edi\EDIProvidersClass;
use common\components\edi\ProviderInterface;
use common\models\edi\EdiProvider;
use common\models\EdiOrder;
use common\models\edi\EdiOrganization;
use common\models\OrderContent;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use Yii;

/**
 * Class Provider
 *
 * @package common\components\edi\providers
 */
class LeradataProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $realization;
    public $content;
    public $ediFilesQueueID = 0;
    private $url;
    private $ediProvider;
    private $token;
    private $intUserID;
    private $providerID;
    public $ediOrganization;
    private $glnCode;
    private $orgID;

    /**
     * Provider constructor.
     */
    public function __construct($ediOrganization)
    {
        $this->ediProvider = new EDIProvidersClass();
        $this->url = \Yii::$app->params['edi_api_data']['edi_api_leradata_url'];
        $this->providerID = parent::getProviderID(self::class);
        $this->ediOrganization = $ediOrganization;
        $this->intUserID = $this->ediOrganization['int_user_id'];
        $this->token = $this->ediOrganization['token'];
        $this->glnCode = $this->ediOrganization['gln_code'];
        $this->orgID = $this->ediOrganization['organization_id'];
    }

    /**
     * Get files list from provider and insert to table
     */
    public function handleFilesList(): void
    {
        try {
            $this->getFilesListForInsertingInQueue();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }
    }

    /**
     * @param $login
     * @param $pass
     * @return null
     * @throws \yii\base\Exception
     */
    public function getFilesListForInsertingInQueue()
    {
        $this->getOneTypeFilesList('pricat');
        $this->getOneTypeFilesList('desadv');
        $this->getOneTypeFilesList('ordrsp');
        return true;
    }

    private function getOneTypeFilesList($type)
    {
        $paramsArray = [
            "docType" => $type,
            "GLNs"    => [$this->glnCode]
        ];

        $obj = $this->executeCurl($paramsArray, 'edi_getDocument');
        if (isset($obj['response'])) {
            $list = $obj['response'];
            if (!empty($list)) {
                foreach ($list as $key => $xml) {
                    $this->ediFilesQueueID = $key;
                    if ($type == 'pricat') {
                        $xml = json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE));
                        $this->realization->handlePriceListUpdating($xml, $this->providerID);
                    } else {
                        $exceptionArray = [
                            'file_id'   => $key,
                            'status'    => parent::STATUS_ERROR,
                            'json_data' => json_encode($xml)
                        ];
                        $simpleXMLElement = json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE));
                        $this->realization->handleOrderResponse($simpleXMLElement, $type, $this->providerID, false, true, $exceptionArray);
                    }
                }
            }
        }
        return [];
    }

    public function sendOrderInfo($order, $done = false): bool
    {
        $transaction = Yii::$app->db_api->beginTransaction();
        $result = false;
        try {
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->ediProvider->getDateData($order);
            $string = $this->realization->getSendingOrderContent($order, $done, $dateArray, $orderContent);
            $result = $this->sendDoc($string, $done);
            $order->updateAttributes(['edi_order' => $order->id]);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollback();
        }
        return $result;
    }

    public function sendDoc(String $string, $done = false): bool
    {
        $action = 'edi_sendDocuments';
        $object = new \SimpleXMLElement($string);
        $dataArray = json_decode(json_encode($object, JSON_UNESCAPED_UNICODE), true, 512, JSON_UNESCAPED_UNICODE);

        if ($done) {
            if (isset($dataArray['HEAD']['PACKINGSEQUENCE']['POSITION']['POSITIONNUMBER'])) {
                $dataArray['HEAD']['PACKINGSEQUENCE']['POSITION'] = [$dataArray['HEAD']['PACKINGSEQUENCE']['POSITION']];
            }
            $dataArray['HEAD']['PACKINGSEQUENCE'] = [$dataArray['HEAD']['PACKINGSEQUENCE']];
        } else {
            if (isset($dataArray['HEAD']['POSITION']['CHARACTERISTIC'])) {
                $dataArray['HEAD']['POSITION']['CHARACTERISTIC'] = [$dataArray['HEAD']['POSITION']['CHARACTERISTIC']];
                $dataArray['HEAD']['POSITION'] = [$dataArray['HEAD']['POSITION']];
            } else {
                foreach ($dataArray['HEAD']['POSITION'] as $key => $value) {
                    $dataArray['HEAD']['POSITION'][$key]['CHARACTERISTIC'] = [$value['CHARACTERISTIC']];
                }
            }
        }

        $dataArray['HEAD'] = [$dataArray['HEAD']];
        $documentType = ($done) ? 'recadv' : 'order';
        $paramsArray = [[
            "docType" => $documentType,
            "doc"     => $dataArray
        ]];
        $array = $this->executeCurl($paramsArray, $action);
        if ($array['response']) {
            return true;
        } else {
            Yii::error("EDI returns error code");
            return false;
        }
    }

    private function executeCurl($paramsArray, $action)
    {
        $requestArray = [
            "token"     => "$this->token",
            "varGln"    => "$this->glnCode",
            "intUserID" => "$this->intUserID",
            "params"    => $paramsArray
        ];
        $payload = json_encode($requestArray, JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->url);
        $postData = "$action=$payload";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $array = json_decode($result, true, 512, JSON_UNESCAPED_UNICODE);
        return (array)$array;
    }

    public function parseFile($content)
    {
        $success = $this->realization->parseFile($content, $this->providerID);
        if ($success) {
            $this->updateQueue($this->ediFilesQueueID, parent::STATUS_HANDLED, '');
        }
    }
}