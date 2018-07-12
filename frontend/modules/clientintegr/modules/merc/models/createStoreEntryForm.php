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
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\getForeignEnterpriseChangesListRequest;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
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
    public $dateOfProduction;
    public $expiryDate;
    public $perishable;
    public $country;
    public $producer;
    public $producer_role;
    public $producer_product_name;

    public function rules()
    {
        return [
            [['productType','product','subProduct','product_name','volume', 'unit','perishable','country','producer','producer_role','producer_product_name'], 'required'],
            [['productType','perishable'],'integer'],
            [['volume'], 'double'],
            [['product', 'subProduct','product_name', 'unit','country','producer','producer_role','producer_product_name','batchID'], 'string', 'max' => 255],
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
            'producer_product_name' => 'Наименование продукции'
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
                $res[$item->guid] = $item->name;
        }
        return $res;
    }

}