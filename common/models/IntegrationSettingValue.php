<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "integration_setting_value".
 *
 * @property int $id Уникальный идентификатор
 * @property int $setting_id Указатель на настройку
 * @property int $org_id Указатель на организацию
 * @property string $value Значение настройки для данной организации
 * @property string $created_at
 * @property string $updated_at
 *
 * @property IntegrationSetting $setting
 */
class IntegrationSettingValue extends yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'integration_setting_value';
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
            [['setting_id', 'org_id', 'value'], 'required'],
            [['setting_id', 'org_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['value'], 'string', 'max' => 255],
            [['setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationSetting::class,
                'targetAttribute' => ['setting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Уникальный идентификатор',
            'setting_id' => 'Указатель на настройку',
            'org_id' => 'Указатель на организацию',
            'value' => 'Значение настройки для данной организации',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(IntegrationSetting::class, ['id' => 'setting_id']);
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => yii\behaviors\TimestampBehavior::class,
                'attributes' => [
                    yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

}
