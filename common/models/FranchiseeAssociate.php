<?php

namespace common\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "franchisee_associate".
 *
 * @property int          $id              Идентификатор записи в таблице
 * @property int          $franchisee_id   Идентификатор франчайзи
 * @property int          $organization_id Идентификатор организации
 * @property int          $self_registered Показатель статуса создания связи организации и франчайзи (0 - связь не
 *           создана, 1 - организация зарегистрировалась сама, 2 - организация зарегистрирована через админку)
 * @property int          $agent_id        Идентификатор пользователя-агента (users)
 * @property string       $created_at      Дата и время создания записи в таблице
 * @property string       $updated_at      Дата и время последнего изменения записи в таблице
 * @property Franchisee   $franchisee
 * @property Organization $organization
 * @property User         $agent
 */
class FranchiseeAssociate extends \yii\db\ActiveRecord
{
    const SELF_REGISTERED = 1;
    const REGISTERED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%franchisee_associate}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['franchisee_id', 'organization_id'], 'required'],
            [['organization_id'], 'unique'],
            [['franchisee_id', 'organization_id', 'self_registered'], 'integer'],
            [['franchisee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Franchisee::className(), 'targetAttribute' => ['franchisee_id' => 'id']],
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
            'franchisee_id'   => 'Franchisee ID',
            'organization_id' => 'Organization ID',
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
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(User::className(), ['id' => 'agent_id']);
    }
}
