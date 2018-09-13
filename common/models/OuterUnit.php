<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "outer_unit".
 *
 * @property int $id
 * @property string $outer_uid ???
 * @property int $service_id ID Сервиса
 * @property string $name Название продукта
 * @property string $iso_code ISO код
 * @property int $is_deleted Статус удаления
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 */
class OuterUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_unit';
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
            [['service_id', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['outer_uid'], 'string', 'max' => 45],
            [['name'], 'string', 'max' => 255],
            [['iso_code'], 'string', 'max' => 12],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'outer_uid' => 'Outer Uid',
            'service_id' => 'Service ID',
            'name' => 'Name',
            'iso_code' => 'Iso Code',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
