<?php

namespace common\models;

use Yii;

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
 */
class CatalogBaseGoods extends \yii\db\ActiveRecord
{
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
            [['cat_id', 'category_id'], 'required'],
            [['cat_id', 'category_id'], 'integer'],
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
        ];
    }
    
    public function search($params,$cat_base_id) {
	    $query = CatalogBaseGoods::find()->where(['cat_id'=>$cat_base_id]);
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
	 
	    $this->addCondition($query, 'id');
	    $this->addCondition($query, 'cat_id', true);
	    $this->addCondition($query, 'category_id', true);
	    $this->addCondition($query, 'article');
	 
	    /* Setup your custom filtering criteria */
	 
	    // filter by person full name
	    /*$query->andWhere('first_name LIKE "%' . $this->fullName . '%" ' .
	        'OR last_name LIKE "%' . $this->fullName . '%"'
	    );*/
	 
	    return $dataProvider;
	}
}
