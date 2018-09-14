<?php

namespace common\models;

use Yii;
use \yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;
use common\components\NestedSetsQuery;
use yii\db\Expression;

/**
 * This is the model class for table "outer_store".
 *
 * @property int $id
 * @property string $outer_uid
 * @property int $service_id
 * @property int $org_id
 * @property string $name
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string $store_type
 * @property int $tree
 * @property int $left
 * @property int $right
 * @property int $level
 * @property int $selected
 * @property int $collapsed
 */
class OuterStore extends ActiveRecord
{
    /**
     * NestedSets model
     * https://yiigist.com/package/creocoder/yii2-nested-sets#!?tab=readme
     * ------------------------------------------------------
     */
    public function behaviors()
    {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::class,
                'treeAttribute' => 'tree',
                'leftAttribute' => 'left',
                'rightAttribute' => 'right',
                'depthAttribute' => 'level'
            ],
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_UPDATE => ['updated_at'],
                    ActiveRecord::EVENT_AFTER_INSERT => ['created_at'],
                ],
                'value' => new Expression('UTC_TIMESTAMP()')
            ],
        ];
    }

    public static function find()
    {
        return new NestedSetsQuery(get_called_class());
    }

    /**
     * ------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_store';
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
            [['outer_uid', 'service_id', 'org_id', 'name'], 'required'],
            [['service_id', 'org_id', 'is_deleted', 'selected', 'collapsed', 'left', 'right', 'tree', 'level'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['outer_uid', 'name', 'store_type'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'outer_uid' => Yii::t('app', 'Outer Uid'),
            'service_id' => Yii::t('app', 'Service ID'),
            'org_id' => Yii::t('app', 'Org ID'),
            'name' => Yii::t('app', 'Name'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'store_type' => Yii::t('app', 'Store Type'),
            'left' => Yii::t('app', 'Left'),
            'right' => Yii::t('app', 'Right'),
            'level' => Yii::t('app', 'Level'),
            'selected' => Yii::t('app', 'Selected'),
            'collapsed' => Yii::t('app', 'Collapsed'),
        ];
    }
}
