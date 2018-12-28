<?php

namespace common\models;

/**
 * This is the model class for table "relation_manager_leader".
 *
 * @property int  $id         Идентификатор записи в таблице
 * @property int  $manager_id Идентификатор пользователя-руководителя
 * @property int  $leader_id  Идентификатор-пользователя-сотрудника, подчинённого руководителя
 *
 * @property User $leader
 * @property User $manager
 */
class RelationManagerLeader extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation_manager_leader}}';
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
            [['manager_id', 'leader_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'manager_id' => 'Manager ID',
            'leader_id'  => 'Leader ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeader()
    {
        return $this->hasOne(User::className(), ['id' => 'leader_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }
}
