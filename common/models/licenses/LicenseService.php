<?php

namespace common\models\licenses;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\models\AllService;

/**
 * This is the model class for table "license_service".
 *
 * @property int $id Уникальный ID
 * @property int $license_id Указатель на ID лицензии
 * @property int $service_id Указатель на ID сервиса
 * @property string $created_at Дата создания
 * @property string $updated_at Дата обновления
 *
 * @property License $license
 * @property AllService $service
 */
class LicenseService extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'license_service';
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
            [['license_id', 'service_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['license_id'], 'exist', 'skipOnError' => true, 'targetClass' => License::class, 'targetAttribute' => ['license_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => AllService::class, 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Уникальный ID',
            'license_id' => 'Указатель на ID лицензии',
            'service_id' => 'Указатель на ID сервиса',
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
    public function getLicense()
    {
        return $this->hasOne(License::class, ['id' => 'license_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(AllService::class, ['id' => 'service_id']);
    }
}
