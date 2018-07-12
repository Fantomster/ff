<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.05.2018
 * Time: 12:03
 */

namespace frontend\modules\clientintegr\modules\merc\models;


use yii\base\Model;

class createStoreEntryForm extends Model {

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
            [['product', 'subProduct','product_name', 'unit','country','producer','producer_role','producer_product_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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

}