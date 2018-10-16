<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\ecom\providers;


use common\components\ecom\AbstractProvider;
use common\components\ecom\ProviderInterface;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use Yii;

/**
 * Class Provider
 *
 * @package common\components\ecom\providers
 */
class KorusProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $client;
    public $realization;

    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->client = \Yii::$app->siteApiKorus;
    }

    /**
     * @param $login
     * @param $pass
     * @return null
     * @throws \yii\base\Exception
     */
    public function getFilesList($login, $pass, $glnCode)
    {
        $action = 'listmb';
        $pricatList = $this->getOneTypeFilesList('PRICAT', $login, $pass, $glnCode, $action);
        $desadvList = $this->getOneTypeFilesList('DESADV', $login, $pass, $glnCode, $action);
        $ordrspList = $this->getOneTypeFilesList('ORDRSP', $login, $pass, $glnCode, $action);
        $list = array_merge($pricatList, $desadvList, $ordrspList);
        if (!count($list)) {
            throw new Exception('No files for ' . $login);
        }
        return $list;
    }


    private function getOneTypeFilesList($type, $login, $pass, $glnCode, $action)
    {
        $relationId = $this->getRelation($type, $login, $pass, $glnCode);
        $soap_request = <<<EOXML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:edi="http://edi-express.esphere.ru/">
   <soapenv:Header/>
   <soapenv:Body>
      <edi:ListMBInput>
         <edi:Name>$login</edi:Name>
         <edi:Password>$pass</edi:Password>
         <edi:RelationId>$relationId</edi:RelationId>
      </edi:ListMBInput>
   </soapenv:Body>
</soapenv:Envelope>
EOXML;
        $array = $this->executeCurl($soap_request, $action);
        $list = $array['ns2ListMBResponse']['ns2Cnt']['ns2mailbox-response']['ns2document-info'] ?? null;
        $trackingIdList = [];
        if (is_iterable($list)) {
            foreach ($list as $key => $value) {
                if (isset($value['ns2tracking-id'])) {
                    $trackingIdList[] = $value['ns2tracking-id'];
                }
            }
        }
        return $trackingIdList;
    }


    /**
     * @param \common\models\Organization $vendor
     * @param String $string
     * @param String $remoteFile
     * @param String $login
     * @param String $pass
     * @return bool
     */
    public function sendDoc(String $string, String $action, String $login, String $pass, $glnCode): bool
    {
        $action = 'send';
        $string = base64_encode($string);
        $relationId = $this->getRelation('ORDERS', $login, $pass, $glnCode);
        $soap_request = <<<EOXML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:edi="http://edi-express.esphere.ru/">
   <soapenv:Header/>
   <soapenv:Body>
      <edi:SendInput>
         <edi:Name>$login</edi:Name>
         <edi:Password>$pass</edi:Password>
         <edi:RelationId>$relationId</edi:RelationId>
         <edi:DocumentContent>$string</edi:DocumentContent>
      </edi:SendInput>
   </soapenv:Body>
</soapenv:Envelope>
EOXML;
        $array = $this->executeCurl($soap_request, $action);

        if ($array['ns2SendResponse']['ns2Res'] == 1) {
            return true;
        } else {
            Yii::error("Ecom returns error code");
            return false;
        }
    }


    private function executeCurl($soap_request, $action)
    {
        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: " . strlen($soap_request),
        );

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, "https://edi-ws.esphere.ru/$action");
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        $res = curl_exec($soap_do);
        curl_close($soap_do);
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $res);
        $xml = new \SimpleXMLElement($response);
        $body = $xml->xpath('//SOAP-ENV:Body')[0];
        $array = json_decode(json_encode((array)$body), TRUE);
        return $array;
    }


    private function getRelation($documentType, $login, $pass, $glnCode)
    {
        $relationId = 0;
        $client = $this->client;
        $res = $client->process(["Name" => $login, 'Password' => $pass]);
        $cnt = $res->Cnt;
        $arr = (array)$cnt;
        $relations = $arr['relation-response'];
        $relations = $relations->relation;
        foreach ($relations as $relation) {
            $rel = (array)$relation;
            if (isset($rel['document-type']) && $rel['document-type'] == $documentType && $rel['partner-iln'] == $glnCode) {
                $relationId = $rel['relation-id'];
            }
        }
        return $relationId;
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
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:edi="http://edi-express.esphere.ru/">
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


    public function getDoc($client, String $fileName, String $login, String $pass, int $ediFilesQueueID, $glnCode): bool
    {
        try {
            $this->updateQueue($ediFilesQueueID, self::STATUS_PROCESSING, '');
            try {
                $content = $this->getDocContent($fileName, $login, $pass, $glnCode);
            } catch (\Throwable $e) {
                $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, $e->getMessage());
                Yii::error($e->getMessage());
                return false;
            }

            if ($content == '') {
                $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, 'No such file');
                return false;
            }
            $dom = new \DOMDocument();
            $dom->loadXML($content);
            $simpleXMLElement = simplexml_import_dom($dom);

        } catch (\Exception $e) {
            Yii::error($e);
            $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, 'Error handling file 2');
            return false;
        }
        return $simpleXMLElement;
    }
}