<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_active_service}}".
 *
 * @property int $user_id Пользователь
 * @property int $organization_id Организация
 * @property int $service_id Выбраный сервис в организации
 *
 * @property Organization $organization
 * @property User $user
 */
class UserActiveService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_active_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'organization_id', 'service_id'], 'required'],
            [['user_id', 'organization_id', 'service_id'], 'integer'],
            [['user_id', 'organization_id'], 'unique', 'targetAttribute' => ['user_id', 'organization_id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'Пользователь'),
            'organization_id' => Yii::t('app', 'Организация'),
            'service_id' => Yii::t('app', 'Выбраный сервис в организации'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
