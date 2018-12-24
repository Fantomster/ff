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
 * @property int $service_id Идентификатор сервиса в таблице all_service
 * @property int $required_moderation Настройка сервиса обязательна к модерации
 *
 * @property IntegrationSettingValue[] $integrationSettingValues
 */
class IntegrationSetting extends \yii\db\ActiveRecord
{

    const TYPE_LIST = [
        1 => 'dropdown_list',
        2 => 'input_text',
        3 => 'password',
        4 => 'dropdown_list',
        5 => 'radio',
        6 => 'input_text',
        7 => 'checkbox',
    ];

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
            [['is_active', 'required_moderation'], 'integer'],
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
            'item_list' => 'Список значение по умолчанию в формате JSON, для отображения при начальном выборе, ' .
                'например { 1: \"Включено\", 2: \"Выключено\"}',
            'required_moderation' => 'Обязателен к модерации',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegrationSettingValues()
    {
        return $this->hasMany(IntegrationSettingValue::class, ['setting_id' => 'id']);
    }

}
