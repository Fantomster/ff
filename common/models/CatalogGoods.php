<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "catalog_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $base_goods_id
 * @property string $price
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property CatalogBaseGoods $baseProduct
 * @property Organization $organization
 */
class CatalogGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id', 'base_goods_id'], 'required'],
            [['cat_id', 'base_goods_id'], 'integer'],
            [['price'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
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
            'base_goods_id' => 'Cat Base Goods ID',
            'price' => 'Price',
            'note' => 'Note',
        ];
    }
    public function search($params,$id) {
	    $query = CatalogGoods::find()->where(['cat_id'=>$id]);
	    //$query->andFilterWhere(['like', 'product', '']);
	    $dataProvider = new ActiveDataProvider([
	        'query' => $query,
	    ]);
	    $dataProvider->setSort([
	        'attributes' => [
	            'id',
                    'cat_id',
                    'base_goods_id',
                    'price',
                    'note',
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
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'base_goods_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id'])->via('baseProduct');
    }
}
//        $query->joinWith(['baseProduct' => function ($query) use ($baseProductTable) {
//            $query->from(['baseProduct' => $baseProductTable]);
//        }]);
