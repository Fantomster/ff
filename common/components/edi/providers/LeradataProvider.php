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
use common\components\edi\ProviderInterface;
use common\models\EdiOrder;
use common\models\EdiOrganization;
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
    public $client;
    public $realization;
    public $content;
    public $ediFilesQueueID;
    private $schema;
    private $wsdl;

    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->client = \Yii::$app->siteApiKorus;
        $this->schema = "http://schemas.xmlsoap.org/soap/envelope/";
        $this->wsdl = "http://edi-express.esphere.ru/";
    }

    /**
     * Get files list from provider and insert to table
     */
    public function handleFilesList($orgId): void
    {
        $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
        if ($ediOrganization) {
            $login = $ediOrganization['login'];
            $pass = $ediOrganization['pass'];
            $glnCode = $ediOrganization['gln_code'];
            try {
                $objectList = $this->getFilesListForInsertingInQueue($login, $pass, $glnCode);
            } catch (\Throwable $e) {
                Yii::error($e->getMessage());
            }
            if (!empty($objectList)) {
                $this->insertFilesInQueue($objectList, $orgId);
            }
        }
    }

    /**
     * @param $login
     * @param $pass
     * @return null
     * @throws \yii\base\Exception
     */
    public function getFilesListForInsertingInQueue($login, $pass, $glnCode)
    {
        $action = 'edi_getDocument';
        $pricatList = $this->getOneTypeFilesList('pricat', $login, $pass, $glnCode, $action);
        $desadvList = $this->getOneTypeFilesList('DESADV', $login, $pass, $glnCode, $action);
        $ordrspList = $this->getOneTypeFilesList('ORDRSP', $login, $pass, $glnCode, $action);
        $list = array_merge($pricatList, $desadvList, $ordrspList);
        if (!count($list)) {
            throw new Exception('No files for ' . $login);
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getFilesList($organizationId): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('edi_files_queue')
            ->where(['status' => [AbstractRealization::STATUS_NEW, AbstractRealization::STATUS_ERROR]])
            ->andWhere(['organization_id' => $organizationId])
            ->all();
    }

    /**
     * @param array $list
     * @throws \yii\db\Exception
     */
    public function insertFilesInQueue(array $list, $orgId)
    {
        $batch = [];
        $files = (new \yii\db\Query())
            ->select(['name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('name')
            ->all();

        foreach ($list as $name) {
            if (!array_key_exists($name, $files)) {
                $batch[] = ['name' => $name, 'organization_id' => $orgId];
            }
        }

        if (!empty($batch)) {
            \Yii::$app->db->createCommand()->batchInsert('edi_files_queue', ['name', 'organization_id'], $batch)->execute();
        }
    }

    private function getOneTypeFilesList($type, $login, $pass, $glnCode, $action)
    {
        $requestArray = [
            "token" => "yN2XiNfSMmNGA6bLtlHKo21bxtbWAx3",
            "varGln" => 9879870002282,
            "intUserID" => 13902,
            "params" => [
/*                "docType" => $type,
                "GLNs" => [$glnCode]*/
            ]
        ];

        $array = $this->executeCurl($requestArray, $action);
        dd($array);
        $list = $array['ns2ListMBResponse']['ns2Cnt']['ns2mailbox-response']['ns2document-info'] ?? null;
        $trackingIdList = [];
        if (is_iterable($list)) {
            foreach ($list as $key => $value) {
                if (isset($value['ns2tracking-id'])) {
                    $trackingIdList[] = $value['ns2tracking-id'];
                } elseif ($key == 'ns2tracking-id') {
                    $trackingIdList[] = $value;
                }
            }
        }
        return $trackingIdList;
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
                    'lang' => Yii::$app->language ?? 'ru'
                ])->execute();
            }

            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->getDateData($order);
            if (!count($orderContent)) {
                Yii::error("Empty order content");
                $transaction->rollback();
                return $result;
            }

            $string = $this->realization->getSendingOrderContent($order, $done, $dateArray, $orderContent);
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
            $result = $this->sendDoc($string, $ediOrganization, $done);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollback();
        }
        return $result;
    }

    /**
     * @param \common\models\Organization $vendor
     * @param String $string
     * @param String $remoteFile
     * @param String $login
     * @param String $pass
     * @return bool
     */
    public function sendDoc(String $string, $ediOrganization, $done = false): bool
    {
        $action = 'send';
        $string = base64_encode($string);
        $login = $ediOrganization['login'];
        $pass = $ediOrganization['pass'];
        $documentType = ($done) ? 'RECADV' : 'ORDERS';
        $relationId = $this->getRelation($documentType, $login, $pass, $ediOrganization['gln_code']);
        $soap_request = <<<EOXML
<soapenv:Envelope xmlns:soapenv="$this->schema" xmlns:edi="$this->wsdl">
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

    private function executeCurl($requestArray, $action)
    {
        $url = 'https://leradata.pro/api/vetis/api.php?МЕТОД=getDocType';
        $payload = json_encode($requestArray);

        $ch = curl_init($url);
# Setup request to send json via POST.

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
# Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
# Send request.
        $result = curl_exec($ch);

        curl_close($ch);
        $array = json_decode($result);
        dd($result);
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

    private function getDateData($order): array
    {
        $arr = [];
        $arr['created_at'] = $this->formatDate($order->created_at ?? '');
        $arr['requested_delivery_date'] = $this->formatDate($order->requested_delivery ?? '');
        $arr['requested_delivery_time'] = $this->formatTime($order->requested_delivery ?? '');
        $arr['actual_delivery_date'] = $this->formatDate($order->actual_delivery ?? '');
        $arr['actual_delivery_time'] = $this->formatTime($order->actual_delivery ?? '');
        return $arr;
    }

    private function formatDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }

    private function formatTime(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('H:i');
    }
}