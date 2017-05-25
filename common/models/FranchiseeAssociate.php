<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "franchisee_associate".
 *
 * @property integer $id
 * @property integer $franchisee_id
 * @property integer $organization_id
 * @property integer $self_registered
 *
 * @property Franchisee $franchisee
 * @property Organization $organization
 */
class FranchiseeAssociate extends \yii\db\ActiveRecord
{
    const SELF_REGISTERED = 0;
    const REGISTERED = 1;
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
    public function rules()
    {
        return [
            [['franchisee_id', 'organization_id'], 'required'],
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
}
