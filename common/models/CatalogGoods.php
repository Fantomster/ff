<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $cat_base_goods_id
 * @property string $price
 * @property string $note
 */
class CatalogGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id', 'cat_base_goods_id'], 'required'],
            [['cat_id', 'cat_base_goods_id'], 'integer'],
            [['price'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
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
            'cat_base_goods_id' => 'Cat Base Goods ID',
            'price' => 'Price',
            'note' => 'Note',
        ];
    }
}
