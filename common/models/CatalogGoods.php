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
 * @property integer $price
 * @property integer $discount
 * @property integer $discount_percent
 * @property integer $discount_fixed
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
    public function scenarios()
    {
         $scenarios = parent::scenarios();
           $scenarios['update'] = ['discount_percent','cat_id'];

           return $scenarios;
    }
    public function rules()
    {
        return [
            [['cat_id', 'base_goods_id'], 'required'],
            [['cat_id', 'base_goods_id'], 'integer'],
            [['price'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 500],
            [['discount'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
            [['discount_percent'],'number','min'=>-100,'max'=>100],
            [['discount_fixed'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/','min' => 0],
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
            'discount' => 'Discount',
            'discount_percent' => 'Discount %',
            'discount_fixed' => 'Discount Fixed',
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
                'discount',
                'discount_percent',
                'discount_fixed'
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public static function searchProductFromCatalogGoods($id,$cat_id){
        if(CatalogGoods::find()->where(['base_goods_id' => $id, 'cat_id' => $cat_id])->exists()){
            return true;
        }else{
            return false;
                
        }
    }
    
}
