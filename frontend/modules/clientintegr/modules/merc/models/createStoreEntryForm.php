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
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\ComplexDate;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\GoodsDate;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Product;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\ProductItem;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockDiscrepancy;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockEntry;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\StockEntryList;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\SubProduct;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Unit;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use yii\base\Model;

class createStoreEntryForm extends Model {

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

    public function rules()
    {
        return [
            [['productType','product','subProduct','product_name','volume', 'unit','perishable','country','producer'], 'required'],
            [['productType','perishable'],'integer'],
            [['volume'], 'double'],
            [['product', 'subProduct','product_name', 'unit','country','producer','producer_role','producer_product_name','batchID', 'country'], 'string', 'max' => 255],
            [['vsd'],'string']
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
            'vsd' => 'Входящий ВСД'
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
        $enterprise = $enterprise->enterprise;
        $business = $business->businessEntity;

        return [ $enterprise->name.'('.
                    $enterprise->address->addressView
                    .')',
                $business->name.', ИНН:'.$business->inn,
        ];
    }

    public static function getUnitList()
    {
        $list = dictsApi::getInstance()->getUnitList();

        $res = [];
        foreach ($list->unitList->unit as $item)
        {
            if($item->last)
                $res[$item->uuid] = $item->name;
        }
        return $res;
    }

    public function getProductList()
    {
        if(empty($this->productType))
            return [];

       $list = productApi::getInstance()->getProductByTypeList($this->productType);

        if(!isset($list->productList->product))
            return [];

        $res = [];
        foreach ($list->productList->product as $item)
        {
            if($item->last)
                $res[$item->guid] = $item->name;
        }
        return $res;
    }

    public function getSubProductList()
    {
        if(empty($this->product))
            return [];
        $list = productApi::getInstance()->getSubProductByProductList($this->product);

        if(!isset($list->subProductList->subProduct))
            return [];

        $res = [];
        foreach ($list->subProductList->subProduct as $item)
        {
            if($item->last)
                $res[$item->guid] = $item->name. " (".$item->code.")";
        }
        return $res;
    }

    public function getProductName()
    {
        if(empty($this->productType) || empty($this->product) || empty($this->subProduct))
            return "";
        $list = productApi::getInstance()->getProductItemList  ($this->productType, $this->product, $this->subProduct);

        if(!isset($list->productItemList->productItem))
            return [];

        $res = [];
        foreach ($list->productItemList->productItem as $item)
        {
            if($item->last)
                $res[] = ['value' => $item->name,
                    'label' => $item->name
                    ];
        }
        return $res;
    }

    public static function getCountryList()
    {

        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;

        $res = [];
        do {
            $list = ikarApi::getInstance()->getAllCountryList($listOptions);
            foreach ($list->countryList->country as $item) {
                if ($item->last)
                    $res[$item->uuid] = $item->name;
            }

            if($list->countryList->count < $list->countryList->total)
                $listOptions->offset += $list->countryList->count;
        } while ($list->countryList->total > ($list->countryList->offset + $list->countryList->count));
        return $res;
    }

    public function getStockDiscrepancy()
    {
        $ID = 'report1';
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
        $stockEntry->batch->productItem->volume = $this->volume;
        $stockEntry->batch->productItem->unit = new Unit();
        $stockEntry->batch->productItem->unit->uuid = $this->unit;


    }

    private function convertDate($date)
    {
        $time = strtotime($date);

        $res = new GoodsDate();
        $res->firstDate = new ComplexDate();
        $res->firstDate->year = date('Y', $time);
    }

}