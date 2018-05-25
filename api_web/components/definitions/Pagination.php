<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Pagination"))
 */
class Pagination
{
    /**
     * @SWG\Property(@SWG\Xml(name="page"), example=1)
     * @var integer
     */
    public $page;

    /**
     * @SWG\Property(@SWG\Xml(name="page_size"), example=12)
     * @var integer
     */
    public $page_size;

    /**
     * @SWG\Property(@SWG\Xml(name="total_page"), example=7)
     * @var integer
     */
    public $total_page;
}