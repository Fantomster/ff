<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="IntegrationServiceList"))
 */
class IntegrationServiceList
{
    /**
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/IntegrationService"))
     */
    public $services;
}