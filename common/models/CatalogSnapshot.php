<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_snapshot".
 *
 * @property int $id
 * @property int $cat_id
 * @property string $created_at
 *
 * @property Catalog $cat
 * @property CatalogSnapshotContent[] $catalogSnapshotContents
 */
class CatalogSnapshot extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_snapshot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id'], 'required'],
            [['cat_id'], 'integer'],
            [['created_at'], 'safe'],
            [['cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalog::className(), 'targetAttribute' => ['cat_id' => 'id']],
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
            'created_at' => 'Created At',
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
    public function getCatalogSnapshotContents()
    {
        return $this->hasMany(CatalogSnapshotContent::className(), ['snapshot_id' => 'id']);
    }
}
