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

    /**
     * @SWG\Property(@SWG\Xml(name="is_allowed_for_franchisee"), example=1)
     * @var integer
     */
    public $is_allowed_for_franchisee;

}


/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Vendor"))
 */
class Vendor {

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
     * @SWG\Property(@SWG\Xml(name="legal_entity"), example="ООО Рога и Копыта")
     * @var string
     */
    public $legal_entity;

    /**
     * @SWG\Property(@SWG\Xml(name="contact_name"), example="Имя контакта")
     * @var string
     */
    public $contact_name;

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

    /**
     * @SWG\Property(@SWG\Xml(name="inn"), example="0001112223")
     * @var string
     */
    public $inn;

    /**
     * @var integer
     * @SWG\Property(@SWG\Xml(name="allow_editing"), example=1)
     */
    public $allow_editing;

    /**
     * @SWG\Property(@SWG\Xml(name="min_order_price"), example=100)
     * @var float
     */
    public $min_order_price;

    /**
     * @SWG\Property(@SWG\Xml(name="min_free_delivery_charge"), example=100)
     * @var float
     */
    public $min_free_delivery_charge;

    /**
     * @SWG\Property(@SWG\Xml(name="disabled_delivery_days"), example={1,2,3,5})
     * @var object
     */
    public $disabled_delivery_days;

    /**
     * @SWG\Property(@SWG\Xml(name="delivery_days"), example={"mon": 0,"tue": 1,"wed": 0,"thu": 0,"fri": 0,"sat": 1,"sun": 1})
     * @var object
     */
    public $delivery_days;
}