<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 12/11/2018
 * Time: 13:03
 */

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="LicenseServer"))
 */
class LicenseService
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example="1")
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="name"), example="R-keeper")
     * @var string
     */
    public $name;

    /**
     * @SWG\Property(@SWG\Xml(name="is_active"), example="1")
     * @var string
     */
    public $is_active;

    /**
     * @SWG\Property(@SWG\Xml(name="created_at"), example="2018-10-15T10:17:45+03:00")
     * @var string
     */
    public $created_at;

    /**
     * @SWG\Property(@SWG\Xml(name="updated_at"), example="2018-10-15T10:17:45+03:00")
     * @var string
     */
    public $updated_at;

    /**
     * @SWG\Property(@SWG\Xml(name="login_allowed"), example="1")
     * @var string
     */
    public $login_allowed;

    /**
     * @SWG\Property(@SWG\Xml(name="to_date"), example="2022-07-20T14:41:44+03:00")
     * @var string
     */
    public $to_date;
}