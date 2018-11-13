<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\realization;

use common\components\edi\AbstractRealization;
use common\components\edi\EDIClass;
use common\components\edi\RealizationInterface;

/**
 * Class Realization
 *
 * @package common\components\edi\realization
 */
class EcomRealization extends AbstractRealization implements RealizationInterface
{
    /**
     * @var \SimpleXMLElement
     */
    public $xml;
    private $edi;

    public function __construct()
    {
        $this->edi = new EDIClass();
        $this->edi->fileName = $this->fileName;
    }

    public function parseFile($content)
    {
        return $this->edi->parseFile($content);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function handlePriceListUpdating($xml): bool
    {
        return $this->edi->handlePriceListUpdating($xml);
    }

    protected function insertGood(int $catID, int $catalogBaseGoodID, float $price): bool
    {
        return $this->edi->insertGood($catID, $catalogBaseGoodID, $price);
    }

    protected function handleOrderResponse(\SimpleXMLElement $simpleXMLElement, $isAlcohol = false)
    {
        return $this->edi->handleOrderResponse($simpleXMLElement, $isAlcohol);
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