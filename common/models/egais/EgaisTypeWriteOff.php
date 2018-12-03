<?php

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property string $type [varchar(255)]
 */
class EgaisTypeWriteOff extends \Yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_type_write_off';
    }

    /**
     * @return object|\yii\db\Connection|null
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
            [['type'], 'required'],
            [['type'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Type'
        ];
    }
}