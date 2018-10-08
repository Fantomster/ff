<?php

/**
 * Class OuterCategory
 * @package api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_category".
 *
 * @property int $id ID записи данных о категории
 * @property string $outer_uid ID записи категории в источнике загрузки данных
 * @property string $parent_outer_uid ID записи родительской категории в источнике загрузки данных
 * @property int $service_id ID сервиса, с помощью которого была произведена загрузка данной категории
 * @property int $org_id ID организации, к которой относится данная категория
 * @property string $name Наименование категории
 * @property int $is_deleted Признак неиспользуемой категории
 * @property string $created_at Метка времени - дата и время создания записи категории
 * @property string $updated_at Метка времени - дата и время последного изменения записи категории
 * @property int $selected Признак отбора данной категории
 * @property int $collapsed Признак свернутости данной категории
 * @property int $tree ID корневого элемента
 * @property int $left Левое включаемое значение выборки типа nested sets
 * @property int $right Правое включаемое значение выборки типа nested sets
 * @property int $level Уровень подчиненности записи (ID родильской записи) для выборки типа nested sets
 */
class OuterCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_category';
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
            [['service_id', 'org_id', 'is_deleted', 'selected', 'collapsed', 'tree', 'left', 'right', 'level'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['parent_outer_uid', 'outer_uid'], 'string', 'max' => 45],
            [['name'], 'string', 'max' => 255],
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
            'parent_outer_uid' => 'Parent Outer Uid',
            'service_id' => 'Service ID',
            'org_id' => 'Org ID',
            'name' => 'Name',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'selected' => 'Selected',
            'collapsed' => 'Collapsed',
            'tree' => 'Tree',
            'left' => 'Left',
            'right' => 'Right',
            'level' => 'Level',
        ];
    }

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

}
