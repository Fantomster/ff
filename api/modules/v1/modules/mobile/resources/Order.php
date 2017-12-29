<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class Order extends \common\models\Order
{
    public $count;
    public $page;
    public $symbol;
    
    public function fields()
    {
        return ['id', 'client_id', 'vendor_id', 'created_by_id', 'accepted_by_id', 'status', 'total_price', 
            'created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment', 'discount', 'discount_type','currency_id', 'symbol'];
    }
    
     /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'client_id', 'vendor_id', 'created_by_id', 'status', 'discount_type','count', 'page'], 'integer'],
            [['total_price', 'discount'], 'number'],
            [['created_at', 'updated_at', 'requested_delivery', 'actual_delivery', 'comment'], 'safe'],
            [['comment'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['accepted_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['accepted_by_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['created_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

}
