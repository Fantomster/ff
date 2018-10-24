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
use common\models\EdiOrder;
use common\models\EdiOrganization;
use common\models\OrderContent;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
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
    public $ediFilesQueueID;
    private $url;
    private $ediProvider;
    private $token;
    private $varGln;
    private $intUserID;


    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->ediProvider = new EDIProvidersClass();
        $this->url = "https://leradata.pro/api/vetis/api.php";
    }

    /**
     * Get files list from provider and insert to table
     */
    public function handleFilesList($orgId): void
    {
        $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
        if ($ediOrganization) {
            $this->token = $ediOrganization['token'];
            $this->varGln = $ediOrganization['gln_code'];
            $this->intUserID = $ediOrganization['int_user_id'];
            try {
                $objectList = $this->getFilesListForInsertingInQueue();
            } catch (\Throwable $e) {
                Yii::error($e->getMessage());
            }
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
            "GLNs"    => [$this->varGln]
        ];

        $obj = $this->executeCurl($paramsArray, 'edi_getDocument');
        if(isset($obj['response'])){
            $list = $obj['response'];
            if(!empty($list)){
                foreach ($list as $xml) {
                    if($type=='pricat'){
                        $this->realization->handlePriceListUpdating($xml);
                    }else{
                        $this->realization->handleOrderResponse($xml);
                    }
                }
            }
        }
        return [];
    }


    public function sendOrderInfo($order, $orgId, $done = false): bool
    {
        $transaction = Yii::$app->db_api->beginTransaction();
        $result = false;
        try {
            $ediOrder = EdiOrder::findOne(['order_id' => $order->id]);
            if (!$ediOrder) {
                Yii::$app->db->createCommand()->insert('edi_order', [
                    'order_id' => $order->id,
                    'lang'     => Yii::$app->language ?? 'ru'
                ])->execute();
            }

            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->ediProvider->getDateData($order);
            $string = $this->realization->getSendingOrderContent($order, $done, $dateArray, $orderContent);
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
            if(!$ediOrganization){
                throw new BadRequestHttpException();
                $transaction->rollback();
            }
            $this->token = $ediOrganization['token'];
            $this->varGln = $ediOrganization['gln_code'];
            $this->intUserID = $ediOrganization['int_user_id'];
            $result = $this->sendDoc($string, $done);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollback();
        }
        return $result;
    }

    /**
     * @param \common\models\Organization $vendor
     * @param String                      $string
     * @param String                      $remoteFile
     * @param String                      $login
     * @param String                      $pass
     * @return bool
     */
    public function sendDoc(String $string, $done = false): bool
    {
        $action = 'edi_sendDocuments';
        $dataArray = new \SimpleXMLElement($string);
        $documentType = ($done) ? 'recadv' : 'order';
        $paramsArray = [
            "docType" => "order",
            "doc" =>  $dataArray
        ];
        $array = $this->executeCurl($paramsArray, $action);

        if ($array['ns2SendResponse']['ns2Res'] == 1) {
            return true;
        } else {
            Yii::error("Ecom returns error code");
            return false;
        }
    }

    private function executeCurl($paramsArray, $action)
    {
        $requestArray = [
            "token"     => "$this->token",
            "varGln"    => "$this->varGln",
            "intUserID" => "$this->intUserID",
            "params"    => $paramsArray
        ];
        $payload = json_encode($requestArray);
        $payload = <<<EOJSON
{
    "token":"yN2XiNfSMmNGA6bLtlHKo21bxtbWAx3",
    "varGln":"9879870002282",
    "intUserID":"13902",
    "params":[{
                "docType":"order",
                "doc":{
                        "DOCUMENTNAME":"220",
                        "NUMBER":"13954",
                        "DATE":"2018-10-24",
                        "DELIVERYDATE":"2018-10-24",
                        "CURRENCY":"RUB",
                        "SUPORDER":"13954",
                        "DOCTYPE":"O",
                        "CAMPAIGNNUMBER":"13954",
                        "ORDRTYPE":"ORIGINAL",
                        "HEAD":[{
                                "SUPPLIER":"9879870002282",
                                "BUYER":"9879870002268",
                                "DELIVERYPLACE":"9879870002268",
                                "SENDER":"9879870002268",
                                "RECIPIENT":"9879870002282",
                                "EDIINTERCHANGEID":"13954",
                                "POSITION":[{
                                                "POSITIONNUMBER":"1",
                                                "PRODUCT":"8",
                                                "ORDEREDQUANTITY":"0.100",
                                                "ORDERUNIT":"0",
                                                "ORDERPRICE":"118.00"
                            }]
            }]
        }
        }
    ]
}
EOJSON;
        //da($payload);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$action=$payload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        da($result);
        curl_close($ch);
        $array = json_decode($result);
        return (array)$array;
    }


    /**
     * @param String $fileName
     * @param String $login
     * @param String $pass
     * @return string
     * @throws \yii\db\Exception
     */
    public function getDocContent(String $fileName, String $login, String $pass, $glnCode): String
    {
        $action = 'receive';
        $relationId = $this->getRelation('PRICAT', $login, $pass, $glnCode);
        if (!$relationId) {
            throw new BadRequestHttpException('no relation');
        }
        $soap_request = <<<EOXML
<soapenv:Envelope xmlns:soapenv="$this->schema" xmlns:edi="$this->wsdl">
   <soapenv:Header/>
   <soapenv:Body>
      <edi:ReceiveInput>
         <edi:Name>$login</edi:Name>
         <edi:Password>$pass</edi:Password>
         <edi:RelationId>$relationId</edi:RelationId>
         <edi:TrackingId>$fileName</edi:TrackingId>   
      </edi:ReceiveInput>
   </soapenv:Body>
</soapenv:Envelope>
EOXML;
        $array = $this->executeCurl($soap_request, $action);
        if ($array['ns2ReceiveResponse']['ns2Res'] != 1) {
            throw new Exception('EComIntegration getList Error №' . $array['ns2ReceiveResponse']['ns2Res']);
        }
        if (!isset($array['ns2ReceiveResponse']['ns2Cnt'])) {
            throw new Exception('EComIntegration getList Error № 1');
        }
        return base64_decode($array['ns2ReceiveResponse']['ns2Cnt']);
    }

    public function getFile($item, $orgId)
    {
        try {
            $this->ediFilesQueueID = $item['id'];
            $this->realization->fileName = $item['name'];
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
            $this->updateQueue($this->ediFilesQueueID, self::STATUS_PROCESSING, '');
            try {
                $content = $this->getDocContent($item['name'], $ediOrganization['login'], $ediOrganization['pass'], $ediOrganization['gln_code']);
            } catch (\Throwable $e) {
                $this->updateQueue($this->ediFilesQueueID, self::STATUS_ERROR, $e->getMessage());
                Yii::error($e->getMessage());
                return false;
            }

            if ($content == '') {
                $this->updateQueue($this->ediFilesQueueID, self::STATUS_ERROR, 'No such file');
                return false;
            }
        } catch (\Exception $e) {
            Yii::error($e);
            $this->updateQueue($this->ediFilesQueueID, self::STATUS_ERROR, 'Error handling file 2');
            return false;
        }
        return $content;
    }

    public function parseFile($content)
    {
        $success = $this->realization->parseFile($content);
        if ($success) {
            $this->updateQueue($this->ediFilesQueueID, parent::STATUS_HANDLED, '');
        } else {
            $this->updateQueue($this->ediFilesQueueID, parent::STATUS_ERROR, 'Error handling file 1');
        }
    }


    public function getFilesList($organizationId)
    {

    }

}