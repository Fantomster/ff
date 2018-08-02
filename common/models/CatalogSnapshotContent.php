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
 * @property int $barcode
 * @property string $edi_supplier_article
 * @property string $ssid
 *
 * @property CatalogSnapshot $snapshot
 */
class CatalogSnapshotContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_snapshot_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['snapshot_id'], 'required'],
            [['snapshot_id', 'status', 'market_place', 'deleted', 'category_id', 'mp_show_price', 'barcode'], 'integer'],
            [['price', 'units'], 'number'],
            [['article', 'product', 'note', 'ed', 'image', 'brand', 'region', 'weight', 'ssid'], 'string', 'max' => 255],
            [['edi_supplier_article'], 'string', 'max' => 30],
            [['snapshot_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogSnapshot::className(), 'targetAttribute' => ['snapshot_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'snapshot_id' => Yii::t('app', 'Snapshot ID'),
            'article' => Yii::t('app', 'Article'),
            'product' => Yii::t('app', 'Product'),
            'status' => Yii::t('app', 'Status'),
            'market_place' => Yii::t('app', 'Market Place'),
            'deleted' => Yii::t('app', 'Deleted'),
            'price' => Yii::t('app', 'Price'),
            'units' => Yii::t('app', 'Units'),
            'category_id' => Yii::t('app', 'Category ID'),
            'note' => Yii::t('app', 'Note'),
            'ed' => Yii::t('app', 'Ed'),
            'image' => Yii::t('app', 'Image'),
            'brand' => Yii::t('app', 'Brand'),
            'region' => Yii::t('app', 'Region'),
            'weight' => Yii::t('app', 'Weight'),
            'mp_show_price' => Yii::t('app', 'Mp Show Price'),
            'barcode' => Yii::t('app', 'Barcode'),
            'edi_supplier_article' => Yii::t('app', 'Edi Supplier Article'),
            'ssid' => Yii::t('app', 'Ssid'),
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
