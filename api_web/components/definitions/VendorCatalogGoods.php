<?php
/**
 * Date: 27.08.2018
 * Time: 16:53
 */

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="VendorCatalogGood"))
 */
class VendorCatalogGood {
    /**
     * @SWG\Property(@SWG\Xml(name="product_id"), example=479912)
     * @var integer
     */
    public $product_id;

    /**
     * @SWG\Property(@SWG\Xml(name="article"), example="11ddzc22")
     * @var string
     */
    public $article;

    /**
     * @SWG\Property(@SWG\Xml(name="name"), example="Тестовый но крутой продукт")
     * @var string
     */
    public $name;

    /**
     * @SWG\Property(@SWG\Xml(name="ed"), example="кг")
     * @var string
     */
    public $ed;

    /**
     * @SWG\Property(@SWG\Xml(name="price"), example=200.1)
     * @var float
     */
    public $price;

    /**
     * @SWG\Property(@SWG\Xml(name="currency_id"), example=1)
     * @var integer
     */
    public $vendor;

    /**
     * @SWG\Property(@SWG\Xml(name="currency"), example="RUB")
     * @var string
     */
    public $currency;
}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="VendorCatalogGoods"))
 */
class VendorCatalogGoods
{
    /**
     * @SWG\Property(@SWG\Xml(name="result"), type="array", @SWG\Items(ref="#/definitions/VendorCatalogGood"))
     */
    public $result;

    /**
     * @SWG\Property(@SWG\Xml(name="result"), ref="#/definitions/Pagination")
     */
    public $pagination;
}

