<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Organization"))
 */
class Organization
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example=1)
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="name"), example="Рога и Копыта")
     * @var string
     */
    public $name;

    /**
     * @SWG\Property(@SWG\Xml(name="phone"), example="+79251112233")
     * @var string
     */
    public $phone;

    /**
     * @SWG\Property(@SWG\Xml(name="email"), example="test@test.ru")
     * @var string
     */
    public $email;

    /**
     * @SWG\Property(@SWG\Xml(name="address"), example="Волгоградский пр., 1, Москва, Россия")
     * @var string
     */
    public $address;

    /**
     * @SWG\Property(@SWG\Xml(name="image"), example="https://fkeeper.s3.amazonaws.com/org-picture/b2d4e76a753e40a60fbb4002339771ca")
     * @var string
     */
    public $image;

    /**
     * @SWG\Property(@SWG\Xml(name="type_id"), example=1)
     * @var integer
     */
    public $type_id;

    /**
     * @SWG\Property(@SWG\Xml(name="type_id"), example="Поставщик")
     * @var string
     */
    public $type;

    /**
     * @SWG\Property(@SWG\Xml(name="rating"), example=4.5)
     * @var float
     */
    public $rating;

    /**
     * @SWG\Property(@SWG\Xml(name="city"), example="Москва")
     * @var string
     */
    public $city;

    /**
     * @SWG\Property(@SWG\Xml(name="administrative_area_level_1"), example="Московская область")
     * @var string
     */
    public $administrative_area_level_1;

    /**
     * @SWG\Property(@SWG\Xml(name="country"), example="Россия")
     * @var string
     */
    public $country;

    /**
     * @SWG\Property(@SWG\Xml(name="about"), example="Очень хорошая компания")
     * @var string
     */
    public $about;

}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Vendor"))
 */
class Vendor extends Organization {
    /**
     * @SWG\Property(@SWG\Xml(name="allow_editing"), example=1)
     * @var integer
     */
    public $allow_editing;
}