<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "catalog_goods".
 *
 * @property integer $id
 * @property integer $cat_id
 * @property integer $cat_base_goods_id
 * @property string $price
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
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
            [['cat_id', 'cat_base_goods_id'], 'required'],
            [['cat_id', 'cat_base_goods_id'], 'integer'],
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
            'cat_base_goods_id' => 'Cat Base Goods ID',
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
                    'cat_base_goods_id',
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
}
