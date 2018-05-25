<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog_temp".
 *
 * @property int $id
 * @property int $cat_id
 * @property int $user_id
 * @property string $excel_file
 * @property string $mapping
 * @property string $index_column
 * @property string $created_at
 *
 * @property Catalog $cat
 * @property CatalogTempContent[] $catalogTempContents
 */
class CatalogTemp extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'catalog_temp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['cat_id', 'user_id', 'excel_file'], 'required'],
            [['cat_id', 'user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['excel_file', 'mapping', 'index_column'], 'string', 'max' => 255],
            [['cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalog::className(), 'targetAttribute' => ['cat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cat_id' => 'Cat ID',
            'user_id' => 'User ID',
            'excel_file' => 'Excel File',
            'mapping' => 'Mapping',
            'index_column' => 'Index Column',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat() {
        return $this->hasOne(Catalog::className(), ['id' => 'cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogTempContents() {
        return $this->hasMany(CatalogTempContent::className(), ['temp_id' => 'id']);
    }

}
