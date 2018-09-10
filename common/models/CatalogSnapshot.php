<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_snapshot".
 *
 * @property int $id
 * @property int $cat_id
 * @property string $main_index
 * @property int $currency_id
 * @property string $created_at
 *
 * @property Catalog $cat
 * @property CatalogSnapshotContent[] $catalogSnapshotContent
 */
class CatalogSnapshot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_snapshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cat_id', 'main_index', 'currency_id'], 'required'],
            [['cat_id', 'currency_id'], 'integer'],
            [['created_at'], 'safe'],
            [['main_index'], 'string', 'max' => 255],
            [['cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalog::className(), 'targetAttribute' => ['cat_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cat_id' => Yii::t('app', 'Cat ID'),
            'main_index' => Yii::t('app', 'Main Index'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(Catalog::className(), ['id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogSnapshotContent()
    {
        return $this->hasMany(CatalogSnapshotContent::className(), ['snapshot_id' => 'id']);
    }
}
