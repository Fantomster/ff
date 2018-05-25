<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="User"))
 */
class UserNoAuth
{
    /**
     * @SWG\Property(@SWG\Xml(name="language"), example="RU")
     * @var string
     */
    public $language;
}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="User"))
 */
class User extends UserNoAuth
{
    /**
     * @SWG\Property(@SWG\Xml(name="token"), example="LFs6jAYHKWB2FZ1qQl1R234PBBIb4BP63WwO7")
     * @var string
     */
    public $token;
}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="UserWithLocation"))
 */
class UserWithLocation extends User
{
    /**
     * @SWG\Property(@SWG\Xml(name="location", wrapped=true), example={"country":"Россия", "region":"", "city":"Москва"})
     * @var object
     */
    public $location;
}