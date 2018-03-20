<?php

namespace api_web\components;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="UserWebApiDefinition"))
 */
class UserWebApiDefinition
{
    /**
     * @SWG\Property(@SWG\Xml(name="token"), example="123asd123")
     * @var string
     */
    public $token;

    /**
     * @SWG\Property(@SWG\Xml(name="language"), example="RU")
     * @var string
     */
    public $language;

    /**
     * @SWG\Property(@SWG\Xml(name="location", wrapped=true), example={"country":"Россия", "region":"", "city":"Москва"})
     * @var object
     */
    public $location;
}