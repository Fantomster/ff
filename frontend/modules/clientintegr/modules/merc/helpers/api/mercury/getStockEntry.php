<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\mercury;

use api\common\models\merc\MercStockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\base\Model;
use yii\helpers\Json;

class getStockEntry extends Model
{
    public $UUID;
    public $GUID;
    public $status;
    public $createDate;
    public $entryNumber;
    public $productType;
    public $product;
    public $subProduct;
    public $globalID;
    public $productName;
    public $article;
    public $volume;
    public $unit;
    public $dateOfProduction;
    public $expiryDate;
    public $owner;
    public $owner_firm;
    public $uuid_vsd;
    public $producer;
    public $producer_country;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UUID' => 'Идентификатор версии записи журнала',
            'GUID' => 'Глобальный идентификатор записи журнала',
            'status' => 'Статус версии записи журнала',
            'createDate' => 'Дата создания записи журнала',
            'entryNumber' => 'Номер записи журнала',
            'productType' => 'Тип продукции',
            'product' => 'Продукция',
            'subProduct' => 'Вид продукции',
            'globalID' => ' (GTIN) - идентификационный номер продукции производителя',
            'productName' => 'Наименование продукции',
            'article' => 'Артикул',
            'volume' => 'Объем продукции',
            'unit' => 'Единица измерения объема партии продукции',
            'dateOfProduction' => 'Дата выработки продукции',
            'expiryDate' => 'Дата окончания срока годности продукции',
            'owner' => 'Хозяйствующий субъект (владелец продукции)',
            'owner_firm' => 'Название предприятия',
            'uuid_vsd' => 'Идентификатор связанного с записью журнала ВСД',
            'producer' => 'Производитель',
            'producer_country' => 'Страна происхождения'
        ];
    }

    public function loadStockEntry($UUID, $last = true, $raw = false)
    {
        $this->UUID = $UUID;
        $stockEntry = MercStockEntry::findOne(['uuid' => $UUID]);

        if ($stockEntry != null) {
            if ($last)
                $stockEntry = MercStockEntry::findOne(['guid' => $stockEntry->guid, 'last' => 1, 'active' => 1]);
        } else
            return null;

        require_once (__DIR__ ."/Mercury.php");
        $data_raw = unserialize($stockEntry->raw_data);

        if ($raw) {
            return $data_raw;
        }

        $this->GUID = $stockEntry->guid;
        $this->status = MercStockEntry::$statuses[$stockEntry->status];

        $this->createDate = $stockEntry->create_date;
        $this->entryNumber = $stockEntry->entryNumber;
        $this->productType = MercStockEntry::$product_types[$stockEntry->product_type];

        $product_raw = productApi::getInstance()->getProductByGuid($data_raw->batch->product->guid);
        $this->product = $product_raw->product->name;

        $sub_product_raw = productApi::getInstance()->getSubProductByGuid($data_raw->batch->subProduct->guid);
        $this->subProduct = $sub_product_raw->subProduct->name;
        $this->globalID = $data_raw->batch->productItem->globalID;

        $this->productName = $stockEntry->product_name;
        $this->article = $stockEntry->article;
        $this->volume = $stockEntry->amount;
        $this->unit = $stockEntry->unit;
        $this->dateOfProduction = $stockEntry->production_date;
        $this->expiryDate = $stockEntry->expiry_date;

        $owner_raw = cerberApi::getInstance()->getBusinessEntityByUuid($data_raw->batch->owner->uuid);
        $owner = $owner_raw->businessEntity;
        $this->owner = (isset($owner)) ? ($owner->name . ', ИНН:' . $owner->inn) : " - ";

        //var_dump($data_raw['batch']['owner']['uuid']); die();

        /*$owner_raw = cerberApi::getInstance()->getEnterpriseByUuid($data_raw['batch']['owner']['uuid']);
        $owner = $owner_raw->enterprise;
        $this->owner_firm = $owner->name . '(' .
            $owner->address->addressView
            . ')';*/

        $this->uuid_vsd = $stockEntry->vsd_uuid;
        $this->producer = $stockEntry->producer_name;
        $this->producer_country = $stockEntry->producer_country;
    }
}