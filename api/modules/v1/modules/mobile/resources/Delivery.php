<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Delivery extends \common\models\Delivery
{
    public $count;
    public $page;
    public $list;
    
    public function fields()
    {
        return ['id', 'vendor_id', 'delivery_charge', 'min_free_delivery_charge', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'min_order_price', 'created_at', 'updated_at' ];
    }
    
    public function rules()
    {
        return [
            [['vendor_id', 'page', 'count'], 'integer'],
            [['list'], 'string'],
            [['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'boolean'],
            [['delivery_charge', 'min_free_delivery_charge', 'min_order_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

}
