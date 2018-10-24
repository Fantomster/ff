<?php

namespace api_web\modules\integration\modules\vetis\api\mercury;

use api\common\models\merc\MercStockEntry;
use api_web\modules\integration\modules\vetis\api\cerber\cerberApi;
use api_web\modules\integration\modules\vetis\api\products\productApi;
use yii\base\Model;

/**
 * Class getStockEntry
 *
 * @package api_web\modules\integration\modules\vetis\api\mercury
 */
class getStockEntry extends Model
{
    /**
     * @var
     */
    public $UUID;
    /**
     * @var
     */
    public $GUID;
    /**
     * @var
     */
    public $status;
    /**
     * @var
     */
    public $createDate;
    /**
     * @var
     */
    public $entryNumber;
    /**
     * @var
     */
    public $productType;
    /**
     * @var
     */
    public $product;
    /**
     * @var
     */
    public $subProduct;
    /**
     * @var
     */
    public $globalID;
    /**
     * @var
     */
    public $productName;
    /**
     * @var
     */
    public $article;
    /**
     * @var
     */
    public $volume;
    /**
     * @var
     */
    public $unit;
    /**
     * @var
     */
    public $dateOfProduction;
    /**
     * @var
     */
    public $expiryDate;
    /**
     * @var
     */
    public $owner;
    /**
     * @var
     */
    public $owner_firm;
    /**
     * @var
     */
    public $uuid_vsd;
    /**
     * @var
     */
    public $producer;
    /**
     * @var
     */
    public $producer_country;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UUID'             => 'Идентификатор версии записи журнала',
            'GUID'             => 'Глобальный идентификатор записи журнала',
            'status'           => 'Статус версии записи журнала',
            'createDate'       => 'Дата создания записи журнала',
            'entryNumber'      => 'Номер записи журнала',
            'productType'      => 'Тип продукции',
            'product'          => 'Продукция',
            'subProduct'       => 'Вид продукции',
            'globalID'         => ' (GTIN) - идентификационный номер продукции производителя',
            'productName'      => 'Наименование продукции',
            'article'          => 'Артикул',
            'volume'           => 'Объем продукции',
            'unit'             => 'Единица измерения объема партии продукции',
            'dateOfProduction' => 'Дата выработки продукции',
            'expiryDate'       => 'Дата окончания срока годности продукции',
            'owner'            => 'Хозяйствующий субъект (владелец продукции)',
            'owner_firm'       => 'Название предприятия',
            'uuid_vsd'         => 'Идентификатор связанного с записью журнала ВСД',
            'producer'         => 'Производитель',
            'producer_country' => 'Страна происхождения'
        ];
    }

    /**
     * @param      $UUID
     * @param bool $last
     * @param bool $raw
     * @return mixed|null
     */
    public function loadStockEntry($UUID, $last = true, $raw = false)
    {
        $this->UUID = $UUID;
        $stockEntry = MercStockEntry::findOne(['uuid' => $UUID]);

        if ($stockEntry != null) {
            if ($last) {
                $stockEntry = MercStockEntry::findOne(['guid' => $stockEntry->guid, 'last' => 1, 'active' => 1]);
            }
        } else
            return null;

        require_once(__DIR__ . "/Mercury.php");
        $data_raw = unserialize($stockEntry->raw_data);

        if ($raw) {
            return $data_raw;
        }

        $this->GUID = $stockEntry->guid;
        $this->status = MercStockEntry::$statuses[$stockEntry->status];

        $this->createDate = $stockEntry->create_date;
        $this->entryNumber = $stockEntry->entryNumber;
        $this->productType = MercStockEntry::$product_types[$stockEntry->product_type];

        $product_raw = productApi::getInstance((\Yii::$app->user->identity)->organization_id)->getProductByGuid($data_raw->batch->product->guid);
        $this->product = isset($product_raw) ? $product_raw->name : null;

        $sub_product_raw = productApi::getInstance((\Yii::$app->user->identity)->organization_id)->getSubProductByGuid($data_raw->batch->subProduct->guid);
        $this->subProduct = isset($sub_product_raw) ? $sub_product_raw->name : null;
        $this->globalID = $data_raw->batch->productItem->globalID;

        $this->productName = $stockEntry->product_name;
        $this->article = $stockEntry->article;
        $this->volume = $stockEntry->amount;
        $this->unit = $stockEntry->unit;
        $this->dateOfProduction = $stockEntry->production_date;
        $this->expiryDate = $stockEntry->expiry_date;

        $owner_raw = cerberApi::getInstance((\Yii::$app->user->identity)->organization_id)->getBusinessEntityByUuid($data_raw->batch->owner->uuid);
        $owner = $owner_raw;
        $this->owner = (isset($owner)) ? ($owner->name . ', ИНН:' . $owner->inn) : " - ";

        $this->uuid_vsd = $stockEntry->vsd_uuid;
        $this->producer = $stockEntry->producer_name;
        $this->producer_country = $stockEntry->producer_country;
    }
}