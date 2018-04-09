<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Order"))
 */
class Order
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example=1)
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="client_id"), example=1)
     * @var integer
     */
    public $client_id;

    /**
     * @SWG\Property(@SWG\Xml(name="vendor_id"), example=2)
     * @var integer
     */
    public $vendor_id;

    /**
     * @SWG\Property(@SWG\Xml(name="created_by_id"), example=2)
     * @var integer
     */
    public $created_by_id;

    /**
     * @SWG\Property(@SWG\Xml(name="accepted_by_id"), example=1)
     * @var integer
     */
    public $accepted_by_id;

    /**
     * @SWG\Property(@SWG\Xml(name="status"), example=7)
     * @var integer
     */
    public $status;

    /**
     * @SWG\Property(@SWG\Xml(name="status_text"), example="Формируется")
     * @var string
     */
    public $status_text;

    /**
     * @SWG\Property(@SWG\Xml(name="total_price"), example=77.02)
     * @var float
     */
    public $total_price;

    /**
     * @SWG\Property(@SWG\Xml(name="created_at"), example="2018-02-06 13:21:18")
     * @var string
     */
    public $created_at;

    /**
     * @SWG\Property(@SWG\Xml(name="updated_at"), example="2018-02-06 16:21:18")
     * @var string
     */
    public $updated_at;

    /**
     * @SWG\Property(@SWG\Xml(name="requested_delivery"), example="2018-02-11 16:21:18")
     * @var string
     */
    public $requested_delivery;

    /**
     * @SWG\Property(@SWG\Xml(name="actual_delivery"), example="2018-02-12 16:21:18")
     * @var string
     */
    public $actual_delivery;

    /**
     * @SWG\Property(@SWG\Xml(name="comment"), example="comment")
     * @var string
     */
    public $comment;

    /**
     * @SWG\Property(@SWG\Xml(name="discount"), example=10.10)
     * @var float
     */
    public $discount;

    /**
     * @SWG\Property(@SWG\Xml(name="discount_type"), example=1)
     * @var float
     */
    public $discount_type;

    /**
     * @SWG\Property(@SWG\Xml(name="currency_id"), example=1)
     * @var integer
     */
    public $currency_id;

    /**
     * @SWG\Property(@SWG\Xml(name="min_order_price"), example=1000.50)
     * @var float
     */
    public $min_order_price;

    /**
     * @SWG\Property(@SWG\Xml(name="delivery_price"), example=500)
     * @var float
     */
    public $delivery_price;

    /**
     * @SWG\Property(@SWG\Xml(name="position_count"), example=1)
     * @var integer
     */
    public $position_count;

    /**
     * @SWG\Property(@SWG\Xml(name="calculateDelivery"), example=0)
     * @var float
     */
    public $calculateDelivery;

    /**
     * @SWG\Property(@SWG\Xml(name="total_price_without_discount"), example=510.00)
     * @var float
     */
    public $total_price_without_discount;
}