<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "integration_setting_from_email".
 *
 * @property int $id
 * @property int $organization_id
 * @property string $server_type
 * @property string $server_host
 * @property int $server_port
 * @property int $server_ssl
 * @property string $user
 * @property string $password
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $organization
 */
class IntegrationSettingFromEmail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'integration_setting_from_email';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id', 'server_type', 'server_host', 'server_port', 'user', 'password'], 'required'],
            [['organization_id', 'server_port', 'server_ssl', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['server_type', 'server_host', 'user', 'password'], 'string', 'max' => 255],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'server_type' => 'Тип сервера',
            'server_host' => 'Сервер',
            'server_port' => 'Порт',
            'server_ssl' => 'SSL',
            'user' => 'Логин',
            'password' => 'Пароль',
            'is_active' => 'Активен',
            'created_at' => 'Дата создания',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasMany(IntegrationInvoice::className(), ['integration_setting_from_email_id' => 'id']);
    }
}
