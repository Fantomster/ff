<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.05.2018
 * Time: 12:03
 */

namespace frontend\modules\clientintegr\modules\merc\models;

use api\common\models\merc\mercDicconst;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\ListOptions;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ikarApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Batch;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\BatchOrigin;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\ComplexDate;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Country;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Enterprise;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\GoodsDate;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Producer;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Product;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\ProductItem;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockDiscrepancy;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\SubProduct;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Unit;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocument;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\base\Model;

class createStoreEntryForm extends Model
{

    const ADD_PRODUCT = 1;
    const INV_PRODUCT = 2;
    const INV_PRODUCT_ALL = 3;

    public $batchID;
    public $productType;
    public $product;
    public $subProduct;
    public $product_name;
    public $volume;
    public $unit;
    public $perishable;
    public $country;
    public $producer;
    public $producer_role;
    public $producer_product_name;
    public $vsd;
    public $dateOfProduction;
    public $expiryDate;
    public $vsd_issueDate;
    public $vsd_issueSeries;
    public $vsd_issueNumber;
    public $type = createStoreEntryForm::ADD_PRODUCT;
    public $raw_stock_entry;
    public $reason;
    public $description;

    public function rules()
    {
        return [
            [['productType', 'product', 'subProduct', 'product_name', 'volume', 'unit', 'perishable', 'country', 'producer', 'vsd_issueNumber'], 'required'],
            [['productType', 'perishable'], 'integer'],
            [['volume'], 'double'],
            [['product', 'subProduct', 'product_name', 'unit', 'country', 'producer', 'producer_role', 'producer_product_name', 'batchID', 'country', 'vsd_issueNumber', 'vsd_issueSeries'], 'string', 'max' => 255],
            [['vsd'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'batchID' => 'Номер производственной партии',
            'productType' => 'Тип продукции',
            'product' => 'Продукция',
            'subProduct' => 'Вид продукции',
            'product_name' => 'Наименование продукции',
            'volume' => 'Объём',
            'unit' => 'Единица измерения',
            'perishable' => '',
            'country' => 'Страна происхождения',
            'producer' => 'Производитель продукции',
            'producer_role' => 'Роль предприятия-производителя продукции',
            'producer_product_name' => 'Наименование продукции',
            'vsd' => 'Входящий ВСД',
            'vsd_issueSeries' => 'Серия бумажного ВСД',
            'vsd_issueNumber' => 'Номер бумажного ВСД',
        ];
    }

    public function getPerishableList()
    {
        return [
            true => 'скоропортящаяся продукция ',
            false => 'не скоропортящаяся продукция '
        ];
    }

    public static function getOwner()
    {
        $ent_guid = mercDicconst::getSetting('enterprise_guid');
        $bis_guid = mercDicconst::getSetting('issuer_id');
        $business = cerberApi::getInstance()->getBusinessEntityByGuid($bis_guid);
        $enterprise = cerberApi::getInstance()->getEnterpriseByGuid($ent_guid);

        return [isset($enterprise) ? $enterprise->name . '(' .
            $enterprise->address->addressView
            . ')' : null,
            isset($business) ? $business->name . ', ИНН:' . $business->inn : null,
        ];
    }

    public static function getUnitList()
    {
        $res = \common\models\vetis\VetisUnit::getUnitList();
        if (!empty($res)) {
            return $res;
        }

        return [];
    }

    public function getProductList()
    {
        if (empty($this->productType)) {
            return [];
        }

        $res = \common\models\vetis\VetisProductByType::getProductByTypeList($this->productType);
        if (!empty($res)) {
            return $res;
        }
        return [];
    }

    public function getSubProductList()
    {
        if (empty($this->product)) {
            return [];
        }

        $res = \common\models\vetis\VetisSubproductByProduct::getSubProductByProductList($this->product);
        if (!empty($res)) {
            return $res;
        }

        return [];
    }


    public function getProductsNamesList()
    {
        if (empty($this->subProduct)) {
            return [];
        }

        $res = \common\models\vetis\VetisProductItem::getProductItemList($this->subProduct);
        if (!empty($res)) {
            return $res;
        }

        return [];
    }


    public function getProductName()
    {
        if (empty($this->productType) || empty($this->product) || empty($this->subProduct)) {
            return "";
        }
        $list = productApi::getInstance()->getProductItemList($this->productType, $this->product, $this->subProduct);

        if (!isset($list->productItemList->productItem)) {
            return [];
        }

        $res = [];
        foreach ($list->productItemList->productItem as $item) {
            if ($item->last && $item->active) {
                $res[] = ['value' => $item->name . " | " . $item->uuid,
                    'label' => $item->name,
                    'uuid' => $item->uuid,
                ];
            }
        }
        return $res;
    }

    public static function getCountryList()
    {
        $res = \common\models\vetis\VetisCountry::getCountryList();
        if (!empty($res)) {
            return $res;
        }

        return [];
    }

    public function getStockDiscrepancy($ID)
    {
        if ($this->type == createStoreEntryForm::ADD_PRODUCT) {
            return $this->getAddStockDiscrepancy($ID);
        }

        return $this->getInvStockDiscrepancy($ID);
    }

    public function getAddStockDiscrepancy($ID)
    {
        $stockDiscrepancy = new StockDiscrepancy();
        $stockDiscrepancy->id = $ID;
        $stockDiscrepancy->resultingList = new StockEntryList();

        $stockEntry = new StockEntry();
        $stockEntry->batch = new Batch();
        $stockEntry->batch->productType = $this->productType;

        $stockEntry->batch->product = new Product();
        $stockEntry->batch->product->guid = $this->product;

        $stockEntry->batch->subProduct = new SubProduct();
        $stockEntry->batch->subProduct->guid = $this->subProduct;

        $stockEntry->batch->productItem = new ProductItem();
        $stockEntry->batch->productItem->name = $this->product_name;
        $stockEntry->batch->volume = $this->volume;
        $stockEntry->batch->unit = new Unit();
        $stockEntry->batch->unit->uuid = $this->unit;

        $stockEntry->batch->dateOfProduction = $this->convertDate($this->dateOfProduction);
        $stockEntry->batch->expiryDate = $this->convertDate($this->expiryDate);

        $stockEntry->batch->batchID = $this->batchID;
        $stockEntry->batch->perishable = (bool)$this->perishable;

        $stockEntry->batch->origin = new BatchOrigin();
        $stockEntry->batch->origin->country = new Country();
        $stockEntry->batch->origin->country->uuid = $this->country;
        $stockEntry->batch->origin->producer = new Producer();
        $stockEntry->batch->origin->producer->role = 'PRODUCER';
        $stockEntry->batch->origin->producer->enterprise = new Enterprise();
        $stockEntry->batch->origin->producer->enterprise->guid = $this->producer;

        $stockEntry->vetDocument = new VetDocument();
        $stockEntry->vetDocument->issueSeries = $this->vsd_issueSeries;
        $stockEntry->vetDocument->issueNumber = $this->vsd_issueNumber;
        $time = strtotime($this->vsd_issueDate->first_date);
        $stockEntry->vetDocument->issueDate = date("Y-m-d", $time);

        $stockDiscrepancy->resultingList->stockEntry = $stockEntry;
        return $stockDiscrepancy;
    }

    public function getInvStockDiscrepancy($ID)
    {
        $stockDiscrepancy = new StockDiscrepancy();
        $stockDiscrepancy->id = $ID;
        $stockDiscrepancy->resultingList = new StockEntryList();

        $stockEntry = $this->raw_stock_entry;
        $stockEntry->batch->volume = ($this->type == createStoreEntryForm::INV_PRODUCT) ? $this->volume : 0;

        $stockDiscrepancy->resultingList->stockEntry = $stockEntry;
        return $stockDiscrepancy;
    }

    private function convertDate($date)
    {
        $time = strtotime($date->first_date);

        $res = new GoodsDate();
        $res->firstDate = new ComplexDate();
        $res->firstDate->year = date('Y', $time);
        $res->firstDate->month = date('m', $time);
        $res->firstDate->day = date('d', $time);
        $res->firstDate->hour = date('h', $time);

        if (isset($date->secondDate)) {
            $time = strtotime($date->second_date);

            $res->secondDate = new ComplexDate();
            $res->secondDate->year = date('Y', $time);
            $res->secondDate->month = date('m', $time);
            $res->secondDate->day = date('d', $time);
            $res->secondDate->hour = date('h', $time);
        }
        return $res;
    }

    public function getReason()
    {
        if ($this->type == createStoreEntryForm::ADD_PRODUCT) {
            return "Добавление по бумажному ВСД";
        }

        if ($this->type == createStoreEntryForm::INV_PRODUCT) {
            return $this->reason;
        }

        return "dsdsds";
    }

    public function getDescription()
    {
        if ($this->type == createStoreEntryForm::ADD_PRODUCT) {
            return "Добавление по бумажному ВСД";
        }

        if ($this->type == createStoreEntryForm::INV_PRODUCT) {
            return $this->description;
        }

        return "Некачественный товар";
    }

}
