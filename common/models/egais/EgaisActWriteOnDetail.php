<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-06
 * Time: 10:47
 */

namespace common\models\egais;

/**
 * @property int $id [int(11)]
 * @property int $org_id [int(11)]
 * @property int $act_write_on_id [int(11)]
 * @property int $identity [int(11)]
 * @property string $in_form_f1_reg_id [varchar(250)]
 * @property string $f2_reg_id [varchar(250)]
 * @property string $status [varchar(250)]
 * @property int $created_at [timestamp]
 * @property string $act_reg_id [varchar(250)]
 * @property int $number [int(11)]
 */
class EgaisActWriteOnDetail extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_act_write_on_details';
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
            [['org_id', 'act_write_on_id', 'in_form_f1_reg_id', 'f2_reg_id', 'act_reg_id'], 'required'],
            [['org_id', 'act_write_on_id', 'identity', 'number'], 'integer'],
            [['in_form_f1_reg_id', 'f2_reg_id', 'status', 'act_reg_id'], 'string'],
            [['created_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Organization id',
            'act_write_on_id' => 'Act write on id',
            'act_reg_id' => 'ActRegId',
            'number' => 'Number',
            'in_form_f1_reg_id' => 'InformF1RegId',
            'f2_reg_id' => 'F2RegId',
            'identity' => 'Identity',
            'status' => 'Status',
            'created_at' => 'Created at'
        ];
    }
}