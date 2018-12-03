<?php

namespace common\models\egais;

/**
 * This is the model class for table "egais_write_off".
 *
 * @property integer $id
 * @property int $org_id [int(11)]
 * @property int $identity [int(11)]
 * @property int $act_number [int(11)]
 * @property string $act_date [varchar(250)]
 * @property int $type_write_off [int(11)]
 * @property string $note [varchar(250)]
 * @property string $status [varchar(250)]
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 */
class EgaisWriteOff extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_write_off';
    }

    /**
     * @return object
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
            [['org_id', 'type_write_off',], 'required'],
            [['org_id', 'identity', 'act_number', 'type_write_off',], 'integer'],
            [['act_date', 'note', 'status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Organization',
            'identity' => 'Identity',
            'act_number' => 'Act number',
            'act_date' => 'Act date',
            'type_write_off' => 'Type write off',
            'note' => 'Note',
            'status' => 'Status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }
}