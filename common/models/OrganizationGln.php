<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "organization_gln".
 *
 * @property int $id
 * @property int $org_id
 * @property string $gln_number
 * @property int $edi_provider_id
 * @property int $gln_default_flag
 *
 * @property Organization $id0
 * @property RoamingMap[] $roamingMaps
 * @property RoamingMap[] $roamingMaps0
 */
class OrganizationGln extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'organization_gln';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['org_id'], 'required'],
            [['org_id', 'edi_provider_id', 'gln_default_flag'], 'integer'],
            [['gln_number'], 'string', 'max' => 45],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'org_id' => 'Org ID',
            'gln_number' => 'Gln Number',
            'edi_provider_id' => 'Edi Provider ID',
            'gln_default_flag' => 'Gln Default Flag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Organization::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoamingMaps()
    {
        return $this->hasMany(RoamingMap::className(), ['acquire_gln_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoamingMaps0()
    {
        return $this->hasMany(RoamingMap::className(), ['vendor_gln_id' => 'id']);
    }
}
