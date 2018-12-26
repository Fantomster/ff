<?php

namespace common\models;

/**
 * This is the model class for table "franchisee_user".
 *
 * @property int        $id            Идентификатор записи в таблице
 * @property int        $user_id       Идентификатор пользователя
 * @property int        $franchisee_id Идентификатор франчайзи
 *
 * @property Franchisee $franchisee
 * @property User       $user
 */
class FranchiseeUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%franchisee_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'franchisee_id'], 'required'],
            [['user_id', 'franchisee_id'], 'integer'],
            [['franchisee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Franchisee::className(), 'targetAttribute' => ['franchisee_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'user_id'       => 'User ID',
            'franchisee_id' => 'Franchisee ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFranchisee()
    {
        return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
