<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Request extends \common\models\Request
{
    public $count;
    public $page;
    
    public function fields()
    {
        return ['id', 'category', 'product', 'comment', 'regular', 'amount', 'rush_order', 'payment_method', 'deferment_payment',
            'responsible_supp_org_id', 'count_views', 'created_at', 'end', 'rest_org_id', 'active_status','count','page'];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'rush_order', 'payment_method', 'responsible_supp_org_id', 'count_views', 'rest_org_id', 'active_status','count', 'page'], 'integer'],
            [['created_at', 'end'], 'safe'],
            [['product', 'comment', 'regular', 'amount', 'deferment_payment'], 'string', 'max' => 255],
        ];
    }
}
