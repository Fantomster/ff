<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogGoods extends \common\models\CatalogGoods
{
    public function fields()
    {
        return ['id', 'cat_id', 'base_goods_id', 'created_at', 'updated_at', 'discount_percent', 'discount', 'discount_fixed', 'price'];
    }
    
   
    public function rules() {
        return [
            [['cat_id', 'base_goods_id'], 'integer'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'], 
            [['discount'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
            [['discount_percent'], 'number', 'min' => -100, 'max' => 100],
            [['discount_fixed'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'min' => 0],
        ];
    }
}
