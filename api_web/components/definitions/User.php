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
     * @SWG\Property(@SWG\Xml(name="token"), example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtaXhjYXJ0LnJ1IiwiYWNjZXNzX3Rva2VuIjoiTEZzNmpBWUhLV0IyRloxcVFsMVIyMzRQQkJJYjRCUDYzV3dPNyJ9.OifBDvnREw9M_Eefr3XdMg8regDdvnSFgYah0A0qj_A")
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