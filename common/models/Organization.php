<?php

namespace common\models;

use Yii;

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
    public static function get_value($id){
        $model = Organization::find()->where(["id" => $id])->one();
        if(!empty($model)){
            return $model;
        }
        return null;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(OrganizationType::className(), ['id' => 'type_id']);
    }
    
    /**
     * get available categories for restaurant
     * 
     * @return array
     */
    public function getRestaurantCategories() {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        $categories = RelationCategory::find()
                ->select(['category.id', 'category.name'])
                ->distinct()
                ->joinWith('category', false)
                ->where(['relation_category.rest_org_id' => $this->id])
                ->orderBy(['category.name' => SORT_ASC])
                ->asArray()
                ->all();
        return $categories;
    }
    
    /**
     * get list of suppliers for selected categories
     * 
     * @return array
     */
    public function getSuppliers($categories) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        $categoriesList = [];
        foreach($categories as $category) {
            if ($category['selected']) {
                $categoriesList[] = $category['id'];
            }
        }
        $vendors = RelationCategory::find()
                ->select(['organization.id', 'organization.name', 'relation_supp_rest.cat_id'])
                ->distinct()
                ->leftJoin('relation_supp_rest', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id')
                ->joinWith('vendor', false)
                ->where(['category_id' => $categoriesList])
                ->andWhere(['relation_category.rest_org_id' => $this->id])
                ->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all();
        return $vendors;
    }
}
