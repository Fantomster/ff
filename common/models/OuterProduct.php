<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_product".
 *
 * @property int $id
 * @property int $service_id ID Сервиса
 * @property int $org_id ID организации
 * @property string $outer_uid Внешний ID
 * @property string $name Название продукта
 * @property string $parent_uid Id родителя
 * @property int $level Уровень
 * @property int $is_deleted Статус удаления
 * @property int $is_category Статус категории
 * @property int $outer_unit_id Внешний ID привязки к таблице outer_unit
 * @property string $comment Комментарий
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 * @property OuterUnit $outerUnit
 */
class OuterProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_product';
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
            [['service_id', 'org_id', 'level', 'is_deleted', 'is_category', 'outer_unit_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['outer_uid', 'name', 'parent_uid'], 'string', 'max' => 255],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service ID',
            'org_id' => 'Org ID',
            'outer_uid' => 'Outer Uid',
            'name' => 'Name',
            'parent_uid' => 'Parent Uid',
            'level' => 'Level',
            'is_deleted' => 'Is Deleted',
            'is_category' => 'Is Category',
            'outer_unit_id' => 'Outer Unit ID',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */     
    public function getOuterUnit()
    {
        return $this->hasOne(OuterUnit::className(), ['id' => 'outer_unit_id']);
    }
}
