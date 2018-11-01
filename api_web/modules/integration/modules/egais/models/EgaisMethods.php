<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\egoisXmlFiles;

class EgaisMethods extends WebApi
{

    /**
     * @var \api_web\modules\integration\modules\egais\helpers\EgaisHelper
     */
    private $helper;

    /**
     * EgoisMethods constructor.
     */
    public function __construct()
    {
        $this->helper = new EgaisHelper();
        parent::__construct();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getQueryRests($request)
    {
        $xml=(new egoisXmlFiles())->QueryRests('030000443640');
        $return = $this->helper->sendEgaisQuery('http://192.168.1.70:8090', 'QueryRests', $xml);
        return $return;
    }
}