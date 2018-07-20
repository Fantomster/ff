<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%all_service}}".
 *
 * @property int $id
 * @property int $type_id
 * @property int $is_active
 * @property string $denom
 * @property string $vendor
 * @property string $created_at
 * @property string $updated_at
 * @property string $log_table
 *
 * @property AllServiceOperation[] $allServiceOperations
 */
class AllService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%all_service}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['denom', 'vendor', 'log_table'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type_id' => Yii::t('app', 'Type ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'denom' => Yii::t('app', 'Denom'),
            'vendor' => Yii::t('app', 'Vendor'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'log_table' => Yii::t('app', 'Log Table'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllServiceOperations()
    {
        return $this->hasMany(AllServiceOperation::className(), ['service_id' => 'id']);
    }
}
