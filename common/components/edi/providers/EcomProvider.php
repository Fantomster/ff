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
class EcomProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $client;
    public $realization;
    public $content;
    public $ediFilesQueueID;

    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->client = \Yii::$app->siteApi;
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
    public function getFilesListForInsertingInQueue($login, $pass)
    {
        $client = $this->client;
        try {
            $object = $client->getList(['user' => ['login' => $login, 'pass' => $pass]]);
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }
        if ($object->result->errorCode != 0) {
            Yii::error('EComIntegration getList Error №' . $object->result->errorCode);
        }
        $list = $object->result->list ?? null;

        if (!$list) {
            Yii::error('No files for ' . $login);
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
            $dateArray = $this->getDateData($order);
            if (!count($orderContent)) {
                Yii::error("Empty order content");
                $transaction->rollback();
                return $result;
            }

            $string = $this->realization->getSendingOrderContent($order, $done, $dateArray, $orderContent);
            $ediOrganization = EdiOrganization::findOne(['organization_id' => $orgId]);
            $result = $this->sendDoc($string, $ediOrganization, $done, $order);
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
    public function sendDoc(String $string, $ediOrganization, $done = false, $order): bool
    {
        $currentDate = date("Ymdhis");
        $fileName = $done ? 'recadv_' : 'order_';
        $remoteFile = $fileName . $currentDate . '_' . $order->id . '.xml';

        $obj = $this->client->sendDoc(['user' => ['login' => $ediOrganization['login'], 'pass' => $ediOrganization['pass']], 'fileName' => $remoteFile, 'content' => $string]);
        if (isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0) {
            return true;
        } else {
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
    public function getDocContent(String $fileName, String $login, String $pass, $glnCode): String
    {
        try {
            $doc = $this->client->getDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $fileName]);
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