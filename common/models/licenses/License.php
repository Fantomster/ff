<?php

namespace common\models\licenses;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "license".
 *
 * @property int $id Уникальный ID
 * @property string $name Наименование лицензии
 * @property int $is_active Флаг активности
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 *
 * @property LicenseService[] $licenseServices
 */
class License extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'license';
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
            [['is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Уникальный ID',
            'name' => 'Наименование лицензии',
            'is_active' => 'Флаг активности',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
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


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicenseServices()
    {
        return $this->hasMany(LicenseService::class, ['license_id' => 'id']);
    }
}
