<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogBaseGoods extends \common\models\CatalogBaseGoods
{
    public $list;
    public $organization_name;
    public $comment;
    public $symbol;
    public $vendor_id;
    public $rest_org_id;
    public $count;
    public $page;
    
    public function fields()
    {
        return ['id', 'cat_id', 'article', 'product', 'status', 'units', 'market_place', 'deleted', 'created_at', 'supp_org_id', 
                'updated_at', 'category_id', 'note', 'ed', 'image', 'brand', 'region', 'weight', 'es_status', 'mp_show_price', 'rating', 'price', 
            'organization_name' , 'comment', 'symbol', 'vendor_id', 'rest_org_id', 'count', 'page'];
    }
    
     /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['cat_id', 'category_id', 'supp_org_id', 'status', 'market_place', 'deleted', 'mp_show_price', 'rating', 'units', 'vendor_id'], 'integer'],
            [['market_place', 'mp_show_price'], 'default', 'value' => 0],
            [['article'], 'required', 'on' => 'uniqueArticle'],
            [['article'], 'string', 'max' => 50],
            [['article'], 'uniqueArticle','when' => function($model) {
            return !empty($model->cat_id);
            }],
            [['product', 'brand', 'region', 'weight'], 'string', 'max' => 255],
            [['product', 'brand', 'ed'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['note'], 'string', 'max' => 255],
            [['ed','list'], 'string', 'max' => 255],
            [['image'], 'image', 'extensions' => 'jpg, jpeg, png', 'maxSize' => 2097152, 'tooBig' => 'Размер файла не должен превышать 2 Мб'], //, 'maxSize' => 4194304, 'tooBig' => 'Размер файла не должен превышать 4 Мб'
            [['units'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?(NULL)?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['price'], 'number', 'min' => 0.1],
        ];
    }
}
