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
        $this->url = \Yii::$app->params['edi_api_data']['edi_api_leradata_url'];
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
        if (isset($obj['response'])) {
            $list = $obj['response'];
            if (!empty($list)) {
                foreach ($list as $key => $xml) {
                    $this->ediFilesQueueID = $key;
                        if ($type == 'pricat') {
                            $xml = json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE));
                            $this->realization->handlePriceListUpdating($key, $xml);
                        } else {
                            $exceptionArray = [
                                'file_id' => $key,
                                'status' => parent::STATUS_ERROR,
                                'json_data' => json_encode($xml)
                            ];
                            $this->realization->handleOrderResponse($xml, $type, false, $exceptionArray);
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
            if (!$ediOrganization) {
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
            "varGln"    => "$this->varGln",
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
        $success = $this->realization->parseFile($content);
        if ($success) {
            $this->updateQueue($this->ediFilesQueueID, parent::STATUS_HANDLED, '');
        }
    }

    /**
     * @return array
     */
    public function getFilesList($organizationId): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'name', 'json_data'])
            ->from('edi_files_queue')
            ->where(['status' => [AbstractRealization::STATUS_NEW, AbstractRealization::STATUS_ERROR]])
            ->andWhere(['organization_id' => $organizationId])
            ->all();
    }

}