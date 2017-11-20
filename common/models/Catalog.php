<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog".
 *
 * @property integer $id
 * @property integer $supp_org_id
 * @property string $name
 * @property integer $status
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 * @property integer $currency_id
 * 
 * @property Vendor $vendor
 * @property Currency $currency
 */
class Catalog extends \yii\db\ActiveRecord
{
    const BASE_CATALOG = 1;
    const CATALOG = 2;

    const NON_CATALOG = 0;
    const CATALOG_BASE_NAME = 'Главный каталог';
    const STATUS_ON = 1;
    const STATUS_OFF = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog';
    }
    //auto created_at && updated_at 
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'supp_org_id', 'type'], 'required'],
            [['supp_org_id', 'type', 'status'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            //['type', 'uniqueBaseCatalog'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'supp_org_id' => 'Org Supp ID',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Create Datetime',
        ];
    }

    public function uniqueBaseCatalog() {
        if ($this->type == 1) {
            $baseCheck = self::find()->where(['supp_org_id' => $this->supp_org_id, 'type' => 1])->all();
            if ($baseCheck) {
                $this->addError('type', 'Может быть только один базовый каталог');
            }
        }
    }

    public static function getNameCatalog($id){
	$catalogName = Catalog::find()
	->where(['id' => $id])->one();
	return $catalogName;
    }
    public static function get_value($id){
        $model = Catalog::find()->where(["id" => $id])->one();
        if(!empty($model)){
            return $model;
        }
        return null;
    }
    public static function GetCatalogs($type, $vendorId = null)
    {
		$catalog = Catalog::find()
		->select(['id','status','name','created_at','currency_id'])
		->where(['supp_org_id' => $vendorId ? $vendorId : \common\models\User::getOrganizationUser(Yii::$app->user->id),'type'=>$type])->all();
		return $catalog;
    }
    
    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }
    
    public function getCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }
}
