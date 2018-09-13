<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "outer_product".
 *
 * @property int $id
 * @property int $service_id ID Сервиса
 * @property int $org_id ID организации
 * @property string $outer_uid ???
 * @property string $name Название продукта
 * @property string $parent_uid ???
 * @property int $level Уровень
 * @property int $is_deleted Статус удаления
 * @property int $is_category ???
 * @property int $outer_unit_id ???
 * @property string $comment Комментарий
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
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
            [['outer_uid', 'name', 'parent_uid'], 'string', 'max' => 45],
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
}
