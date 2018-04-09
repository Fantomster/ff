<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 09.04.2018
 * Time: 9:13
 */

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Product"))
 */
class Product
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example=1)
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="product"), example="Тестовый товар, сам добавил")
     * @var string
     */
    public $product;

    /**
     * @SWG\Property(@SWG\Xml(name="catalog_id"), example=331)
     * @var integer
     */
    public $catalog_id;

    /**
     * @SWG\Property(@SWG\Xml(name="category_id"), example=2)
     * @var integer
     */
    public $category_id;

    /**
     * @SWG\Property(@SWG\Xml(name="price"), example=200.10)
     * @var float
     */
    public $price;

    /**
     * @SWG\Property(@SWG\Xml(name="rating"), example=3.5)
     * @var float
     */
    public $rating;

    /**
     * @SWG\Property(@SWG\Xml(name="supplier"), example="ООО Рога и Копыта")
     * @var string
     */
    public $supplier;

    /**
     * @SWG\Property(@SWG\Xml(name="brand"), example="UNITY Production")
     * @var string
     */
    public $brand;

    /**
     * @SWG\Property(@SWG\Xml(name="article"), example="TOVAR1110077")
     * @var string
     */
    public $article;

    /**
     * @SWG\Property(@SWG\Xml(name="ed"), example="шт")
     * @var string
     */
    public $ed;

    /**
     * @SWG\Property(@SWG\Xml(name="units"), example=14.001)
     * @var float
     */
    public $units;

    /**
     * @SWG\Property(@SWG\Xml(name="currency"), example="RUB")
     * @var string
     */
    public $currency;

    /**
     * @SWG\Property(@SWG\Xml(name="image"), example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAaQAAA==")
     * @var string
     */
    public $image;

    /**
     * @SWG\Property(@SWG\Xml(name="in_basket"), example=1.05)
     * @var float
     */
    public $in_basket;
}