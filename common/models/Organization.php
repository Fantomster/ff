<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "organization".
 *
 * @property integer $id
 * @property integer $type_id
 * @property string $name
 * @property string $city
 * @property string $address
 * @property string $zip_code
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property string $created_at
 * @property string $updated_at
 *
 * @property OrganizationType $type
 */
class Organization extends \yii\db\ActiveRecord
{
    const TYPE_RESTAURANT = 1;
    
    const TYPE_SUPPLIER = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'name'], 'required'],
            [['type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'city', 'address', 'zip_code', 'phone', 'email', 'website'], 'string', 'max' => 255],
            [['name', 'city', 'address', 'zip_code', 'phone', 'website'], 'filter', 'filter'=>'\yii\helpers\HtmlPurifier::process'],
            [['email'], 'email'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationType::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type',
            'name' => 'Название',
            'city' => 'Город',
            'address' => 'Адрес',
            'zip_code' => 'Индекс',
            'phone' => 'Телефон',
            'email' => 'Email',
            'website' => 'Веб-сайт',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    public static function getOrganization($id){
	    $getOrganization = Organization::find()
		->where(['id' => $id])->one();  
		return $getOrganization;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(OrganizationType::className(), ['id' => 'type_id']);
    }
}
