<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_base_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $category_id
 * @property string $article
 * @property string $product
 * @property string $units
 * @property string $price
 */
class CatalogBaseGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_base_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id', 'category_id'], 'required'],
            [['cat_id', 'category_id'], 'integer'],
            [['article', 'price'], 'string', 'max' => 50],
            [['product'], 'string', 'max' => 255],
            [['units'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cat_id' => 'Cat ID',
            'category_id' => 'Category ID',
            'article' => 'Article',
            'product' => 'Product',
            'units' => 'Units',
            'price' => 'Price',
        ];
    }
}
