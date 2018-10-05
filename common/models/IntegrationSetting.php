<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "integration_setting".
 *
 * @property int $id Уникальный идентификатор настройки
 * @property string $name Наименование настройки
 * @property string $default_value Значение по умолчанию
 * @property string $comment Комментарий - подробное описание настройки, отображается на фронт
 * @property string $type Тип настройки - вып. список, полее ввода и т.п.
 * @property int $is_active Флаг активности объекта
 * @property string $item_list Список значение по умолчанию в формате JSON, для отображения при начальном выборе, например { 1: "Включено", 2: "Выключено"}
 *
 * @property IntegrationSettingValue[] $integrationSettingValues
 */
class IntegrationSetting extends yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'integration_setting';
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
            [['name', 'comment'], 'required'],
            [['type'], 'string'],
            [['is_active'], 'integer'],
            [['name', 'default_value', 'comment', 'item_list'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Уникальный идентификатор настройки',
            'name' => 'Наименование настройки',
            'default_value' => 'Значение по умолчанию',
            'comment' => 'Комментарий - подробное описание настройки, отображается на фронт',
            'type' => 'Тип настройки - вып. список, полее ввода и т.п.',
            'is_active' => 'Флаг активности объекта',
            'item_list' => 'Список значение по умолчанию в формате JSON, для отображения при начальном выборе, '.
                'например { 1: \"Включено\", 2: \"Выключено\"}',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegrationSettingValues()
    {
        return $this->hasMany(IntegrationSettingValue::class, ['setting_id' => 'id']);
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
