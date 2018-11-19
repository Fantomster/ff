<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "outer_agent_name_waybill".
 *
 * @property int    $id
 * @property int    $agent_id
 * @property string $name
 * @property OuterAgent $agent
 */
class OuterAgentNameWaybill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_agent_name_waybill';
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
            [['agent_id'], 'integer'],
            [['name'], 'string', 'max' => 400],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'       => 'ID',
            'agent_id' => 'Agent ID',
            'name'     => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(OuterAgent::class, ['id' => 'agent_id']);
    }
}
