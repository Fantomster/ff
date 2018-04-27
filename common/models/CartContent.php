<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "cart_content".
 *
 * @property int $id
 * @property int $cart_id
 * @property int $vendor_id
 * @property int $product_id
 * @property int $currency_id
 * @property string $product_name
 * @property double $quantity
 * @property double $price
 * @property double $units
 * @property string $comment
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Cart $cart
 * @property array $product
 * @property Organization $vendor
 */
class CartContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cart_content';
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cart_id' => Yii::t('app', 'Cart ID'),
            'vendor_id' => Yii::t('app', 'Vendor ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'product_name' => Yii::t('app', 'Product Name'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price' => Yii::t('app', 'Price'),
            'units' => Yii::t('app', 'Units'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'currency_id' => Yii::t('app', 'Currency ID'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->cart->updated_at = new Expression('NOW()');
        $this->cart->save();

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::className(), ['id' => 'cart_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
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
