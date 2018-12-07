<?php

/**
 * Class OuterCategory
 *
 * @package   api_web\module\integration
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author    Mixcart
 * @module    WEB-API
 * @version   2.0
 */

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;
use common\components\NestedSetsQuery;

/**
 * This is the model class for table "outer_category".
 *
 * @property int    $id               ID записи данных о категории
 * @property string $outer_uid        ID записи категории в источнике загрузки данных
 * @property string $parent_outer_uid ID записи родительской категории в источнике загрузки данных
 * @property int    $service_id       ID сервиса, с помощью которого была произведена загрузка данной категории
 * @property int    $org_id           ID организации, к которой относится данная категория
 * @property string $name             Наименование категории
 * @property int    $is_deleted       Признак неиспользуемой категории
 * @property string $created_at       Метка времени - дата и время создания записи категории
 * @property string $updated_at       Метка времени - дата и время последного изменения записи категории
 * @property int    $selected         Признак отбора данной категории
 * @property int    $collapsed        Признак свернутости данной категории
 * @property int    $tree             ID корневого элемента
 * @property int    $left             Левое включаемое значение выборки типа nested sets
 * @property int    $right            Правое включаемое значение выборки типа nested sets
 * @property int    $level            Уровень подчиненности записи (ID родильской записи) для выборки типа nested sets
 * @method isLeaf()
 */
class OuterCategory extends ActiveRecord
{

    /**
     * NestedSets model
     * https://yiigist.com/package/creocoder/yii2-nested-sets#!?tab=readme
     * ------------------------------------------------------
     */
    public function behaviors()
    {
        return [
            'tree'      => [
                'class'          => NestedSetsBehavior::class,
                'treeAttribute'  => 'tree',
                'leftAttribute'  => 'left',
                'rightAttribute' => 'right',
                'depthAttribute' => 'level'
            ],
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function find()
    {
        return new NestedSetsQuery(get_called_class());
    }

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
            'id'               => 'ID',
            'outer_uid'        => 'Outer Uid',
            'parent_outer_uid' => 'Parent Outer Uid',
            'service_id'       => 'Service ID',
            'org_id'           => 'Org ID',
            'name'             => 'Name',
            'is_deleted'       => 'Is Deleted',
            'created_at'       => 'Created At',
            'updated_at'       => 'Updated At',
            'selected'         => 'Selected',
            'collapsed'        => 'Collapsed',
            'tree'             => 'Tree',
            'left'             => 'Left',
            'right'            => 'Right',
            'level'            => 'Level',
        ];
    }

    public function selectedParent()
    {
        /** @var OuterCategory $parent */
        $parent = $this->parents(1)->one();
        if (!empty($parent) && !$parent->isRoot()) {
            if ($this->selected == 1) {
                $parent->selected = 1;
            } else {
                $childrenSelectedCount = $parent->children(1)->andWhere(['selected' => 1])->count();
                if ($childrenSelectedCount == 0) {
                    $parent->selected = 0;
                }
            }
            $parent->save();
        }
    }
}
