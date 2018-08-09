<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_temp_content".
 *
 * @property int $id
 * @property int $temp_id
 * @property string $article
 * @property string $product
 * @property string $price
 * @property double $units
 * @property string $note
 * @property string $ed
 *
 * @property CatalogTemp $temp
 */
class CatalogTempContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_temp_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['temp_id'], 'required'],
            [['temp_id'], 'integer'],
            [['price', 'units'], 'number'],
            [['article', 'product', 'note', 'ed'], 'string', 'max' => 255],
            [['temp_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogTemp::className(), 'targetAttribute' => ['temp_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'temp_id' => 'Temp ID',
            'article' => 'Article',
            'product' => 'Product',
            'price' => 'Price',
            'units' => 'Units',
            'note' => 'Note',
            'ed' => 'Ed',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemp()
    {
        return $this->hasOne(CatalogTemp::className(), ['id' => 'temp_id']);
    }
}
