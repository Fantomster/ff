<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-04
 * Time: 11:43
 */

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property int $org_id [int(11)]
 * @property int $number [int(11)]
 * @property string $act_date [varchar(250)]
 * @property string $note [varchar(250)]
 * @property int $type_charge_on [int(11)]
 * @property string $status [varchar(250)]
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 * @property string $reply_id [varchar(250)]
 */
class EgaisActWriteOn extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_act_write_on';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id', 'type_charge_on'], 'required'],
            [['org_id', 'type_charge_on', 'number'], 'integer'],
            [['act_date', 'note', 'status', 'reply_id'], 'string'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Organization',
            'number' => 'Number',
            'act_date' => 'Act date',
            'type_charge_on' => 'Type charge on',
            'note' => 'Note',
            'status' => 'Status',
            'reply_id' => 'Query id',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }
}