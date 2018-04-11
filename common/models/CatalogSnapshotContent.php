<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_snapshot_content".
 *
 * @property int $id
 * @property int $snapshot_id
 * @property string $article
 * @property string $product
 * @property int $status
 * @property int $market_place
 * @property int $deleted
 * @property string $price
 * @property double $units
 * @property int $category_id
 * @property string $note
 * @property string $ed
 * @property string $image
 * @property string $brand
 * @property string $region
 * @property string $weight
 * @property int $mp_show_price
 *
 * @property CatalogSnapshot $snapshot
 */
class CatalogSnapshotContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_snapshot_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['snapshot_id'], 'required'],
            [['snapshot_id', 'status', 'market_place', 'deleted', 'category_id', 'mp_show_price'], 'integer'],
            [['price', 'units'], 'number'],
            [['article', 'product', 'note', 'ed', 'image', 'brand', 'region', 'weight'], 'string', 'max' => 255],
            [['snapshot_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogSnapshot::className(), 'targetAttribute' => ['snapshot_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'snapshot_id' => 'Snapshot ID',
            'article' => 'Article',
            'product' => 'Product',
            'status' => 'Status',
            'market_place' => 'Market Place',
            'deleted' => 'Deleted',
            'price' => 'Price',
            'units' => 'Units',
            'category_id' => 'Category ID',
            'note' => 'Note',
            'ed' => 'Ed',
            'image' => 'Image',
            'brand' => 'Brand',
            'region' => 'Region',
            'weight' => 'Weight',
            'mp_show_price' => 'Mp Show Price',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSnapshot()
    {
        return $this->hasOne(CatalogSnapshot::className(), ['id' => 'snapshot_id']);
    }
}
