<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "white_list".
 *
 * @property integer $id
 * @property integer $organization_id
 * @property string $info
 * @property string $created_at
 * @property string $updated_at
 * @property string $signed
 * @property string $legal_entity
 * @property string $legal_address
 * @property string $legal_email
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bank_name
 * @property string $bik
 * @property string $correspondent_account
 * @property string $checking_account
 * @property string $phone
 * @property boolean $partnership
 *
 * @property Organization $organization
 * @property CatalogBaseGoods $catalogBaseGoods
 */
class WhiteList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'white_list';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id'], 'required'],
            [['organization_id'], 'integer'],
            [['info'], 'string'],
            [['created_at', 'updated_at', 'partnership'], 'safe'],
            [['signed', 'legal_entity', 'legal_address', 'legal_email', 'inn', 'kpp', 'ogrn', 'bank_name', 'bik', 'correspondent_account', 'checking_account', 'phone'], 'string', 'max' => 255],
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
            'organization_id' => 'Organization ID',
            'info' => 'Поле для заметок',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'signed' => 'Подписант',
            'legal_entity' => 'Юридическое название',
            'legal_address' => 'Юридический адрес',
            'legal_email' => 'Официальный email',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'bank_name' => 'Банк',
            'bik' => 'БИК',
            'correspondent_account' => 'р/с',
            'checking_account' => 'к/с',
            'phone' => 'Телефон',
            'partnership' => 'Наш партнер',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }
    public function getCatalogBaseGoods()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['supp_org_id' => 'organization_id']);
    }
}
