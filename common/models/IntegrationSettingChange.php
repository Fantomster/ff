<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "integration_setting_change".
 *
 * @property int                $id
 * @property int                $org_id                 Указатель на ID организации
 * @property int                $integration_setting_id Указатель на ID сервиса интеграции
 * @property string             $old_value              Старое значение настройки
 * @property string             $new_value              Новое значение настройки
 * @property int                $changed_user_id        Указатель на ID пользователя который запросил изменения
 * @property int                $confirmed_user_id      Указатель на ID пользователя который подтвердил изменения
 * @property int                $is_active              Активность настройки 1-активна, 0-не активна
 * @property string             $created_at             Дата создания запроса на изменения настройки
 * @property string             $updated_at             Дата последнего изменения
 * @property string             $confirmed_at           Дата подтвержения настройки
 *
 * @property IntegrationSetting $integrationSetting
 * @property Organization       $organization
 * @property int                $rejected_user_id [int(11)]  Указатель на ID пользователя который отменил запрос о
 *           изменении
 * @property int                $rejected_at      [timestamp]  Дата отмены изменения
 */
class IntegrationSettingChange extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integration_setting_change}}';
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
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['org_id', 'integration_setting_id', 'new_value', 'changed_user_id'], 'required'],
            [['org_id', 'integration_setting_id', 'changed_user_id', 'confirmed_user_id', 'rejected_user_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at', 'confirmed_at', 'rejected_at'], 'safe'],
            [['old_value', 'new_value'], 'string', 'max' => 255],
            [['integration_setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationSetting::class, 'targetAttribute' => ['integration_setting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'org_id'                 => 'Org ID',
            'integration_setting_id' => 'Integration Setting ID',
            'old_value'              => 'Старое значение',
            'new_value'              => 'Новое значение',
            'changed_user_id'        => 'Создан',
            'confirmed_user_id'      => 'Подтвердил',
            'rejected_user_id'       => 'Отменил',
            'is_active'              => 'Активность',
            'created_at'             => 'Дата создания',
            'updated_at'             => 'Дата обновления',
            'confirmed_at'           => 'Дата принятия',
            'rejected_at'            => 'Дата отмены',
            'org_name'               => 'Организация',
            'setting_name'           => 'Настройка',
            'setting_comment'        => 'Описание настройки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegrationSetting()
    {
        return $this->hasOne(IntegrationSetting::class, ['id' => 'integration_setting_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    /**
     * @return int|string
     */
    public static function count()
    {
        return self::find()->where(['is_active' => true])->count();
    }
}
