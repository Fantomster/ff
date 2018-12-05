<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:08 PM
 */

namespace common\components\edi;

use common\models\Catalog;
use common\models\Currency;
use common\models\edi\EdiFilesQueue;
use common\models\Order;
use common\models\Organization;
use common\models\RelationSuppRest;
use yii\db\Expression;

/**
 * Class AbstractRealization
 *
 * @package common\components\edi
 */
abstract class AbstractRealization
{

    /**
     *
     */
    const STATUS_NEW = 1;
    /**
     *
     */
    const STATUS_PROCESSING = 2;
    /**
     *
     */
    const STATUS_ERROR = 3;
    /**
     *
     */
    const STATUS_HANDLED = 4;

    /**
     * @var
     */
    public $fileId;

    /**
     * @var
     */
    public $fileName;

    /**
     * @var
     */
    public $fileType;

    public $xml;
    public $edi;

    public function __construct()
    {
        $this->edi = new EDIClass();
        $this->edi->fileName = $this->fileName;
    }

    /**
     * @param int    $status
     * @param String $errorText
     * @throws \yii\db\Exception
     */
    protected function updateQueue(int $status, String $errorText): void
    {
        \Yii::$app->db->createCommand()->update(EdiFilesQueue::tableName(), ['updated_at' => new Expression('NOW()'), 'status' => $status, 'error_text' => $errorText], 'id=' . $this->fileId)->execute();
    }

    /**
     * check gln code for organization and check orderId if file dont have pricat prefix
     *
     * @param $content
     * @param $fileName
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function checkOrgIdAndOrderId($content, $fileName)
    {
        $supplier = $this->getStringBetweenTags($content, '<SUPPLIER>', '</SUPPLIER>');
        $updateResult = $this->addOrgIdToFile($this->fileId, $supplier);
        if (!$updateResult) {
            $this->updateQueue(self::STATUS_ERROR, 'Dont find organization with gln = ' . $supplier);
            return false;
        }
        if (strpos($fileName, 'pricat') !== 0) {
            $orderNumber = $this->getStringBetweenTags($content, '<ORDERNUMBER>', '</ORDERNUMBER>');
            if (is_numeric($orderNumber)) {
                $order = Order::findOne(['id' => $orderNumber]);
                if (is_null($order) || !$order) {
                    $this->updateQueue(self::STATUS_ERROR, 'Dont find order with id = ' . $orderNumber);
                    return false;
                }
            } else {
                $this->updateQueue(self::STATUS_ERROR, 'Number dont numeric with id = ' . $orderNumber);
                return false;
            }
        }

        return true;
    }

    /**
     * add org id to file in queue table
     *
     * @var integer $id
     * @var string  $glnCode
     * @return boolean
     * */
    private function addOrgIdToFile($id, $glnCode)
    {
        $orgId = (new \yii\db\Query())
            ->select(['organization_id'])
            ->from('edi_organization')
            ->where(['gln_code' => $glnCode])
            ->one();

        if (!$orgId) {
            return false;
        }

        try {
            \Yii::$app->db->createCommand()->update('edi_files_queue', ['organization_id' => $orgId['organization_id']], 'id=' . $id)->execute();
        } catch (\Throwable $t) {
            \Yii::error($t->getMessage() . 'error on pdate id=' . $id . 'gln = ' . $glnCode, __METHOD__);
        }
        return true;
    }

    /**
     * Return string between $startTag and $endTag
     *
     * @param $string
     * @param $startTag
     * @param $endTag
     * @return bool|string
     */
    private function getStringBetweenTags($string, $startTag, $endTag)
    {
        $start = strpos($string, $startTag) + strlen($startTag);
        $end = strpos($string, $endTag);
        if (!$start || !$end) {
            return false;
        }
        return substr($string, $start, $end - $start);
    }

    /**
     * @param Organization $organization
     * @param Currency     $currency
     * @param Organization $rest
     * @return int
     * @throws \Exception
     */
    protected function createCatalog(Organization $organization, Currency $currency, Organization $rest): int
    {
        $catalog = new Catalog();
        $catalog->type = Catalog::CATALOG;
        $catalog->supp_org_id = $organization->id;
        $catalog->name = $organization->name;
        $catalog->status = Catalog::STATUS_ON;
        $catalog->created_at = new Expression('NOW()');
        $catalog->updated_at = new Expression('NOW()');
        $catalog->currency_id = $currency->id ?? 1;
        $catalog->save();
        $catalogID = $catalog->id;

        if (!$catalog->save()) {
            throw new \Exception($catalog->getErrorSummary(true));
        }

        $rel = new RelationSuppRest();
        $rel->rest_org_id = $rest->id;
        $rel->supp_org_id = $organization->id;
        $rel->cat_id = $catalogID;
        $rel->invite = 1;
        $rel->created_at = new Expression('NOW()');
        $rel->updated_at = new Expression('NOW()');
        $rel->status = RelationSuppRest::CATALOG_STATUS_ON;
        $rel->save();
        return $catalogID;
    }

    /**
     * @param int   $catID
     * @param int   $catalogBaseGoodID
     * @param float $price
     * @return bool
     * @throws \yii\db\Exception
     */
    public function insertGood(int $catID, int $catalogBaseGoodID, float $price, int $vat = null): bool
    {
        $res = Yii::$app->db->createCommand()->insert('catalog_goods', [
            'cat_id'        => $catID,
            'base_goods_id' => $catalogBaseGoodID,
            'created_at'    => new Expression('NOW()'),
            'updated_at'    => new Expression('NOW()'),
            'price'         => $price,
            'vat'           => $vat
        ])->execute();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function parseFile($content, $providerID)
    {
        return $this->edi->parseFile($content, $providerID);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function handlePriceListUpdating($xml, $providerID): bool
    {
        return $this->edi->handlePriceListUpdating($xml, $providerID);
    }

    public function handleOrderResponse($simpleXMLElement, $documentType, $providerID, $isAlcohol = false, $isLeraData = false, $exceptionArray = [])
    {
        return $this->edi->handleOrderResponse($simpleXMLElement, $documentType, $providerID, $isAlcohol, $isLeraData, $exceptionArray);
    }

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent)
    {
        return $this->edi->getSendingOrderContent($order, $done, $dateArray, $orderContent);
    }

    /**
     * @return array
     */
    public function getFileList(): array
    {
        return $this->edi->getFileList();
    }
}