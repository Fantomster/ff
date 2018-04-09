<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="CartItems"))
 */
class CartItems
{
    /**
     * @SWG\Property(@SWG\Xml(name="order"), ref="#/definitions/Order")
     */
    public $order;

    /**
     * @SWG\Property(@SWG\Xml(name="organization"), ref="#/definitions/Organization")
     */
    public $organization;

    /**
     * @SWG\Property(@SWG\Xml(name="items"), type="array", @SWG\Items(ref="#/definitions/Product"))
     */
    public $items = [];
}