<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cart_content".
 *
 * @property int              $id           Идентификатор записи в таблице
 * @property int              $cart_id      Идентификатор корзины
 * @property int              $vendor_id    Идентификатор организации-поставщика данного товара
 * @property int              $product_id   Идентификатор товара, отложенного в корзину
 * @property string           $product_name Наименование товара, отложенного в корзину
 * @property double           $quantity     Количество данного товара, отложенного в корзину
 * @property double           $price        Цена данного товара
 * @property double           $units        Единица измерения данного товара
 * @property string           $comment      Комментарий сотрудника ресторана к данному товару
 * @property string           $created_at   Дата и время создания записи в таблице
 * @property string           $updated_at   Дата и время последнего изменения записи в таблице
 * @property int              $currency_id  Идентификатор валюты
 *
 * @property Cart             $cart
 * @property Currency         $currency
 * @property CatalogBaseGoods $product
 * @property Organization     $vendor
 */
class CartContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart_content}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cart_id', 'vendor_id', 'product_id'], 'required'],
            [['cart_id', 'vendor_id', 'product_id', 'currency_id'], 'integer'],
            [['quantity', 'price', 'units'], 'number'],
            [['comment'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_name'], 'string', 'max' => 255],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::className(), 'targetAttribute' => ['cart_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogBaseGoods::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('app', 'ID'),
            'cart_id'      => Yii::t('app', 'Cart ID'),
            'vendor_id'    => Yii::t('app', 'Vendor ID'),
            'product_id'   => Yii::t('app', 'Product ID'),
            'product_name' => Yii::t('app', 'Product Name'),
            'quantity'     => Yii::t('app', 'Quantity'),
            'price'        => Yii::t('app', 'Price'),
            'units'        => Yii::t('app', 'Units'),
            'comment'      => Yii::t('app', 'Comment'),
            'created_at'   => Yii::t('app', 'Created At'),
            'updated_at'   => Yii::t('app', 'Updated At'),
            'currency_id'  => Yii::t('app', 'Currency ID'),
        ];
    }

    /**
     * @return array|Cart|null|\yii\db\ActiveRecord
     */
    public function getCart()
    {
        return Cart::find()->cache(3600)->where(['id' => $this->cart_id])->one();
    }

    /**
     * @return array|Currency|null|\yii\db\ActiveQuery|\yii\db\ActiveRecord
     */
    public function getCurrency()
    {
        return Currency::find()->cache(3600)->where(['id' => $this->currency_id])->one();
    }

    /**
     * @return array
     */
    public function getProduct()
    {
        return (new \api_web\helpers\Product())->findFromVendor($this->product_id, $this->vendor_id, $this->cart->organization_id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }
}
