<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "integration_setting_change".
 *
 * @property int $id
 * @property int $org_id
 * @property int $integration_setting_id
 * @property string $old_value
 * @property string $new_value
 * @property int $changed_user_id
 * @property int $confirmed_user_id
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property string $confirmed_at
 *
 * @property IntegrationSetting $integrationSetting
 */
class IntegrationSettingChange extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'integration_setting_change';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     * @throws \yii\base\InvalidConfigException
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
            [['org_id', 'integration_setting_id', 'new_value', 'changed_user_id', 'confirmed_user_id'], 'required'],
            [['org_id', 'integration_setting_id', 'changed_user_id', 'confirmed_user_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at', 'confirmed_at'], 'safe'],
            [['old_value', 'new_value'], 'string', 'max' => 255],
            [['integration_setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationSetting::className(), 'targetAttribute' => ['integration_setting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Org ID',
            'integration_setting_id' => 'Integration Setting ID',
            'old_value' => 'Old Value',
            'new_value' => 'New Value',
            'changed_user_id' => 'Changed User ID',
            'confirmed_user_id' => 'Confirmed User ID',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'confirmed_at' => 'Confirmed At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegrationSetting()
    {
        return $this->hasOne(IntegrationSetting::className(), ['id' => 'integration_setting_id']);
    }
}
