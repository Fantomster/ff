<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "roaming_map".
 *
 * @property int $id
 * @property int $acquire_id
 * @property int $acquire_gln_id
 * @property int $acquire_provuder_id
 * @property int $vendor_id
 * @property int $vendor_gln_id
 * @property int $vendor_provider_id
 *
 * @property OrganizationGln $acquireGln
 * @property Organization $acquire
 * @property EdiProvider $id0
 * @property OrganizationGln $vendorGln
 * @property Organization $vendor
 */
class RoamingMap extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roaming_map';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acquire_id', 'acquire_provuder_id', 'vendor_id', 'vendor_provider_id'], 'required'],
            [['acquire_id', 'acquire_gln_id', 'acquire_provuder_id', 'vendor_id', 'vendor_gln_id', 'vendor_provider_id'], 'integer'],
            [['acquire_gln_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationGln::className(), 'targetAttribute' => ['acquire_gln_id' => 'id']],
            [['acquire_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['acquire_id' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => EdiProvider::className(), 'targetAttribute' => ['id' => 'id']],
            [['vendor_gln_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationGln::className(), 'targetAttribute' => ['vendor_gln_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'acquire_id' => 'Acquire ID',
            'acquire_gln_id' => 'Acquire Gln ID',
            'acquire_provuder_id' => 'Acquire Provuder ID',
            'vendor_id' => 'Vendor ID',
            'vendor_gln_id' => 'Vendor Gln ID',
            'vendor_provider_id' => 'Vendor Provider ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcquireGln()
    {
        return $this->hasOne(OrganizationGln::className(), ['id' => 'acquire_gln_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcquire()
    {
        return $this->hasOne(Organization::className(), ['id' => 'acquire_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(EdiProvider::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorGln()
    {
        return $this->hasOne(OrganizationGln::className(), ['id' => 'vendor_gln_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }
}
