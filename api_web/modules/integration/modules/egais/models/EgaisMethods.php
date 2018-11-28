<?php

namespace api_web\modules\integration\modules\egais\models;

use api_web\components\WebApi;
use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use api_web\modules\integration\modules\egais\classes\egoisXmlFiles;
use api\common\models\egais\egaisSettings;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;

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
     * @throws \yii\base\InvalidConfigException
     */
    public function getQueryRests($request)
    {
        if (empty($request)) {
            $settings = (new egaisSettings())::find()->where(['org_id' => $this->helper->orgId])->one();
            $xml = (new egoisXmlFiles())->QueryRests($settings->fsrar_id);
            $return = $this->helper->sendEgaisQuery($settings->egais_url, $xml, 'QueryRests');

            return ['result' => $return];
        }

        return ['result' => false];
    }

    public function setEgaisSettings($request)
    {
        return (new EgaisHelper())->setSettings($request);
    }

    /**
     * @param $request
     * @return bool|string|array
     * @throws \Exception
     */
    public function getAllIncomingDoc($request)
    {
        $orgId = empty($request) || empty($request['org_id'])
            ? $this->user->organization_id
            : $request['org_id'];

        $settings = EgaisSettings::find()->where(['org_id' => $orgId])->one();

        if (empty($settings)) {
            throw new BadRequestHttpException('Organization not found!');
        }

        return $this->helper->getAllIncomingDoc($settings->egais_url, $request);
    }

    /**
     * @param $request
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function getOneIncomingDoc($request)
    {
        if (empty($request) || empty($request['type']) || empty($request['id'])) {
            throw new BadRequestHttpException('The request invalid!');
        }

        if (!in_array(strtoupper($request['type']), EgaisHelper::$type_document)) {
            throw new BadRequestHttpException('Type document not found!');
        }

        $orgId = empty($request['org_id'])
            ? $this->user->organization_id
            : $request['org_id'];

        $settings = EgaisSettings::find()->where(['org_id' => $orgId])->one();

        if (empty($settings)) {
            throw new BadRequestHttpException('Organization not found!');
        }

        return $this->helper->getOneDocument($settings->egais_url, $request);
    }
}