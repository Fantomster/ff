<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cart_content".
 *
 * @property int $id
 * @property int $cart_id
 * @property int $vendor_id
 * @property int $product_id
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
            [['cart_id', 'vendor_id', 'product_id'], 'integer'],
            [['quantity', 'price', 'units'], 'number'],
            [['comment'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_name'], 'string', 'max' => 255],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::className(), 'targetAttribute' => ['cart_id' => 'id']],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::className(), ['id' => 'cart_id']);
    }

    /**
     * @return array
     */
    public function getProduct()
    {
        $relation = RelationSuppRest::findOne(['rest_org_id' => $this->cart->organization_id, 'supp_org_id' => $this->vendor_id]);

        if (empty($relation) || $relation->cat_id == 0) {
            return [];
        }

        $product = CatalogBaseGoods::findOne(['id' => $this->product_id])->getAttributes();

        if ($product_options = CatalogGoods::find()->where(['cat_id' => $relation->cat_id, 'base_goods_id' => $this->product_id])->one()) {
            $product['price'] = $product_options->price;
            $product['discount'] = $product_options->discount;
            $product['discount_percent'] = $product_options->discount_percent;
            $product['discount_fixed'] = $product_options->discount_fixed;
            $product['cat_id'] = $product_options->cat_id;
        }
        return $product;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }
}
