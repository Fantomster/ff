<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="IntegrationProviderList"))
 */
class IntegrationProviderList
{
    /**
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/IntegrationProvider"))
     */
    public $providers;
}