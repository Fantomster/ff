<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="array", @SWG\Xml(name="CartItems"), @SWG\Items(ref="#/definitions/CartItem"))
 */
class CartItems
{
}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="CartItems"))
 */
class CartItem
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example=3803)
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="delivery_cost"), example=0)
     * @var float
     */
    public $delivery_cost;

    /**
     * @SWG\Property(@SWG\Xml(name="vendor"), ref="#/definitions/Vendor")
     */
    public $vendor;

    /**
     * @SWG\Property(@SWG\Xml(name="items"), type="array", @SWG\Items(ref="#/definitions/Product"))
     */
    public $items = [];
}
