<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_unit".
 *
 * @property int    $id
 * @property string $outer_uid  Внешний ID
 * @property int    $service_id ID Сервиса
 * @property string $name       Название продукта
 * @property int    $org_id     ID организации
 * @property string $iso_code   ISO код
 * @property int    $is_deleted Статус удаления
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
     * @return null|object|\yii\db\Connection the database connection used by this AR class.
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db_api');
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
            'id'         => 'ID',
            'outer_uid'  => 'Outer Uid',
            'service_id' => 'Service ID',
            'name'       => 'Name',
            'iso_code'   => 'Iso Code',
            'is_deleted' => 'Is Deleted',
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
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    public function getInnerName($outerName, $denom)
    {
        $allService = AllService::findOne(['denom' => $denom]);
        if ($allService) {
            $unit = self::findOne(['outer_uid' => $outerName, 'service_id' => $allService->id]);
            if ($unit) {
                return $unit->name;
            }
        }

        return $outerName;
    }

    public function getOuterName($innerName, $denom)
    {
        $allService = AllService::findOne(['denom' => $denom]);
        if ($allService) {
            $unit = self::findOne(['name' => $innerName, 'service_id' => $allService->id]);
            if ($unit) {
                return $unit->outer_uid;
            }
        }
        return $innerName;
    }
}
