<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "franchisee_associate".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property integer $organization_id
 * @property integer $self_registered
 * @property integer $agent_id
 * @property string $created_at
 * @property string $updated_at 
 *
 * @property Franchisee $franchisee
 * @property Organization $organization
 */
class FranchiseeAssociate extends \yii\db\ActiveRecord
{
    const SELF_REGISTERED = 1;
    const REGISTERED = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'franchisee_associate';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'franchisee_id' => 'Franchisee ID',
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
