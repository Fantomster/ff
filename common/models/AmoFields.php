<?php

namespace common\models;

/**
 * This is the model class for table "amo_fields".
 *
 * @property int    $id                  Идентификатор записи в таблице
 * @property string $amo_field           Значение поля FIELDS из формы на лендинге
 * @property int    $responsible_user_id Идентификатор ответственного менеджера
 * @property int    $pipeline_id         Идентификатор "воронки"
 */
class AmoFields extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%amo_fields}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amo_field'], 'required'],
            [['amo_field'], 'string', 'max' => 255],
            [['responsible_user_id', 'pipeline_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amo_field'           => 'Значение поля FIELDS[sitepage] из формы на лендинге(напр. franch)',
            'responsible_user_id' => 'ID ответственного менеджера(responsible_user_id)',
            'pipeline_id'         => 'ID воронки(pipeline_id)',
        ];
    }
}
