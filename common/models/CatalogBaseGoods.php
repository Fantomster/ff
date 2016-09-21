<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "catalog_base_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $category_id
 * @property string $article
 * @property string $product
 * @property string $units
 * @property string $price
 * @property integer $status
 * @property integer $market_place
 * @property integer $deleted
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property Organization $vendor
 */
class CatalogBaseGoods extends \yii\db\ActiveRecord
{
	const STATUS_ON = 1;
	const STATUS_OFF = 0;
	
	const MARKETPLACE_ON = 1;
	const MARKETPLACE_OFF = 0;
	
	const DELETED_ON = 1;
	const DELETED_OFF = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_base_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id'], 'required'],
            [['cat_id', 'category_id','status','market_place','deleted'], 'integer'],
            [['article', 'price'], 'string', 'max' => 50],
            [['product'], 'string', 'max' => 255],
            [['units'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cat_id' => 'Cat ID',
            'category_id' => 'Category ID',
            'article' => 'Article',
            'product' => 'Product',
            'units' => 'Units',
            'price' => 'Price',
            'status'=> 'Status',
            'market_place'=> 'Market_place',
            'deleted'=> 'Deleted'
        ];
    }
    
    public function search($params,$id) {
	    $query = CatalogBaseGoods::find()->where(['cat_id'=>$id,'deleted'=>'0']);
	    $query->andFilterWhere(['like', 'product', '']);
	    $dataProvider = new ActiveDataProvider([
	        'query' => $query,
	    ]);
	    $dataProvider->setSort([
	        'attributes' => [
	            'id',
				'cat_id',
				'category_id',
				'article',
				'product',
				'units',
				'price',
	        ]
	    ]);
	 
	    if (!($this->load($params) && $this->validate())) {
	        return $dataProvider;
	    }
	 
	    /* Setup your custom filtering criteria */
		
	    // filter by person full name
	    /*$query->andWhere('first_name LIKE "%' . $this->fullName . '%" ' .
	        'OR last_name LIKE "%' . $this->fullName . '%"'
	    );*/
	 
	    return $dataProvider;
	}
	/*public function delete_product($id){
	$Catalog = common\models\Catalog::findOne(['id' => $id]);    
	$Catalog->status = $status;
	$Catalog->update();
	}*/
	public static function GetCatalog()
    {
		$catalog = CatalogBaseGoods::find()
		->where(['supp_org_id' => \common\models\User::getOrganizationUser(Yii::$app->user->id),'type'=>\common\models\Catalog::BASE_CATALOG])->all();   
		return $catalog;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }
}
