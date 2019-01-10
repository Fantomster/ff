<?php

namespace common\models;

/**
 * This is the model class for table "manager_associate".
 *
 * @property int          $id              Идентификатор записи в таблице
 * @property int          $manager_id      Идентификатор пользователя с ролью Руководитель
 * @property int          $organization_id Идентификатор организации
 * @property User         $manager
 * @property Organization $organization
 */
class ManagerAssociate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%manager_associate}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['manager_id', 'organization_id'], 'required'],
            [['manager_id', 'organization_id'], 'integer'],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'manager_id'      => 'Manager ID',
            'organization_id' => 'Organization ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * checks if manager is associated with client
     *
     * @param integer $client_id
     * @param integer $vendor_id
     * @param integer $manager_id
     * @return boolean
     */
    public static function isAssociated($client_id, $manager_id)
    {
        return self::find()->where(['manager_id' => $manager_id, 'organization_id' => $client_id])->exists();
    }
}
