<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="IntegrationService"))
 */
class IntegrationService
{
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example="1")
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="type_id"), example="1")
     * @var string
     */
    public $type_id;

    /**
     * @SWG\Property(@SWG\Xml(name="is_active"), example="1")
     * @var string
     */
    public $is_active;

    /**
     * @SWG\Property(@SWG\Xml(name="denom"), example="R-keeper")
     * @var string
     */
    public $denom;

    /**
     * @SWG\Property(@SWG\Xml(name="vendor"), example="UCS")
     * @var string
     */
    public $vendor;

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
     * @SWG\Property(ref="#/definitions/LicenseService")
     */
    public $license;

}