<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\egoisXmlFiles;
use api\common\models\egais\egaisSettings;

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
        if (empty($request)) {
            $settings = (new egaisSettings())::find()->where(['org_id' => $this->helper->orgId])->one();
            $xml = (new egoisXmlFiles())->QueryRests($settings->fsrar_id);
            $return = $this->helper->sendEgaisQuery($settings->egais_url, $xml,'QueryRests');
            return ['result' => $return];
        }
        return ['result' => false];
    }

    public function setEgaisSettings($request)
    {
        return (new EgaisHelper())->setSettings($request);
    }
}