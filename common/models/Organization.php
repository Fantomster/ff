<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

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
 * @property Delivery $delivery
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
        $categories = ArrayHelper::map(Category::find()
                ->select(['id', 'name'])
                ->orderBy(['name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        return $categories;
    }
    
    /**
     * get list of suppliers for selected categories
     * 
     * @return array
     */
    public function getSuppliers($category_id = '', $all = false) {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return [];
        }
        $query = RelationCategory::find()
                ->select(['organization.id', 'organization.name'])
                ->distinct()
                ->leftJoin('relation_supp_rest', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id')
                ->joinWith('vendor', false)
                ->where(['relation_category.rest_org_id' => $this->id]);
        if ($category_id) {
            $query = $query->andWhere(['category_id' => $category_id]);
        }
                
        $vendors = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        return $all ? array_merge(['0' => 'Все'], $vendors) : $vendors;
    }
    
    /**
     * get list of clients
     * 
     * @return array
     */
    public function getClients() {
        if ($this->type_id !== Organization::TYPE_SUPPLIER) {
            return [];
        }
        $query = RelationCategory::find()
                ->select(['organization.id', 'organization.name'])
                ->distinct()
                ->leftJoin('relation_supp_rest', 'relation_category.rest_org_id = relation_supp_rest.rest_org_id')
                ->joinWith('client', false)
                ->where(['relation_category.supp_org_id' => $this->id]);
                
        $clients = ArrayHelper::map($query->orderBy(['organization.name' => SORT_ASC])
                ->asArray()
                ->all(), 'id', 'name');
        return array_merge(['0' => 'Все'], $clients);
    }
    
    /**
     *  get catalogs list for sqldataprovider for order creation
     *  
     *  @return string
     */
    public function getCatalogs($vendor_id = '', $category_id = '') {
        if ($this->type_id !== Organization::TYPE_RESTAURANT) {
            return '0';
        }
        $query = RelationSuppRest::find()
                ->select(['relation_supp_rest.cat_id'])
                ->where(['relation_supp_rest.rest_org_id' => $this->id]);
        if ($category_id) {
            $query = $query->leftJoin('relation_category', 'relation_category.supp_org_id = relation_supp_rest.supp_org_id')
                    ->andWhere(['relation_category.category_id' => $category_id]);
        }
        if ($vendor_id) {
            $query = $query->andWhere(['relation_supp_rest.supp_org_id' => $vendor_id]);
        }
        $catalogs = ArrayHelper::getColumn($query->asArray()->all(), 'cat_id');
        return implode (",", $catalogs);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery() {
        return $this->hasOne(Delivery::className(), ['vendor_id' => 'id']);
    }
}
