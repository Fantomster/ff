<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\providers;

use common\components\edi\AbstractProvider;
use common\components\edi\EDIClass;
use common\components\edi\ProviderInterface;
use common\models\EdiOrder;
use common\models\Journal;
use common\models\OrderContent;
use yii\base\Exception;
use Yii;

/**
 * Class Provider
 *
 * @package common\components\edi\providers
 */
class EcomProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $client;
    public $realization;
    public $content;
    public $ediFilesQueueID = 0;
    private $providerID;
    public $ediOrganization;
    private $login;
    private $pass;
    private $glnCode;
    private $orgID;
    private $remoteFile;

    /**
     * Provider constructor.
     */
    public function __construct($ediOrganization)
    {
        $this->client = \Yii::$app->siteApi;
        $this->providerID = parent::getProviderID(self::class);
        $this->ediOrganization = $ediOrganization;
        $this->login = $this->ediOrganization['login'];
        $this->pass = $this->ediOrganization['pass'];
        $this->glnCode = $this->ediOrganization['gln_code'];
        $this->orgID = $this->ediOrganization['organization_id'];
    }

    /**
     * Get files list from provider and insert to table
     */
    public function handleFilesList()
    {
        $objectList = parent::handleFilesList();
        if (!empty($objectList)) {
            $this->insertFilesInQueue($objectList, $this->orgID);
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
        $client = $this->client;
        try {
            $object = $client->getList(['user' => ['login' => $this->login, 'pass' => $this->pass]]);
        } catch (\Throwable $e) {
            return false;
        }
        if ($object->result->errorCode != 0) {
            return false;
        }
        $list = $object->result->list ?? null;
        return $list;
    }

    public function sendOrderInfo($order, $done = false): bool
    {
        $transaction = Yii::$app->db_api->beginTransaction();
        $result = false;
        try {
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->getDateData($order);
            if (!count($orderContent)) {
                Yii::error("Empty order content");
                $transaction->rollback();
                return $result;
            }
            $string = $this->realization->getSendingOrderContent($order, $done, $dateArray, $orderContent);
            $result = $this->sendDoc($string, $done, $order);
            $order->updateAttributes(['edi_order' => $this->remoteFile ?? $order->id]);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
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
    public function sendDoc(String $string, $done = false, $order): bool
    {
        $currentDate = date("Ymdhis");
        $fileName = $done ? 'recadv_' : 'order_';
        $this->remoteFile = $fileName . $currentDate . '_' . $order->id . '.xml';
        $obj = $this->client->sendDoc(['user' => ['login' => $this->login, 'pass' => $this->pass], 'fileName' => $this->remoteFile, 'content' => $string]);
        if (isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0) {
            //$journalMessage = Yii::t("app", 'По заказу {order} отправлен файл {file}', ['order' => $order->id, 'file' => $this->remoteFile]);
            //EDIClass::writeEdiDataToJournal($this->orgID, $journalMessage);
            return true;
        } else {
            $journalMessage = Yii::t("app", 'Ecom вернул код ошибки ') . $obj->result->errorCode;
            EDIClass::writeEdiDataToJournal($this->orgID, $journalMessage, 'error');
            Yii::error("Ecom returns error code");
            return false;
        }
    }

    /**
     * @param String $fileName
     * @param String $login
     * @param String $pass
     * @return string
     * @throws \yii\db\Exception
     */
    public function getDocContent(String $fileName): String
    {
        try {
            $doc = $this->client->getDoc(['user' => ['login' => $this->login, 'pass' => $this->pass], 'fileName' => $fileName]);
        } catch (\Throwable $e) {
            $this->updateQueue($this->ediFilesQueueID, self::STATUS_ERROR, $e->getMessage());
            Yii::error($e->getMessage());
            return false;
        }

        if (!isset($doc->result->content)) {
            $this->updateQueue($this->ediFilesQueueID, self::STATUS_ERROR, 'No such file');
            return false;
        }

        $content = $doc->result->content;
        return $content;
    }

    public function getFile($item)
    {
        try {
            $this->ediFilesQueueID = $item['id'];
            $this->realization->fileName = $item['name'];
            $this->updateQueue($this->ediFilesQueueID, self::STATUS_PROCESSING, '');
            try {
                $content = $this->getDocContent($item['name']);
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
        $success = $this->realization->parseFile($content, $this->providerID, $this->realization->fileName);
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