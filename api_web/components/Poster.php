<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-10
 * Time: 12:45
 */

namespace api_web\components;

use api_web\exceptions\ValidationException;
use api_web\modules\integration\classes\documents\Waybill;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue as ISV;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\web\BadRequestHttpException;

/**
 * Class Poster
 *
 * @package api_web\components
 */
class Poster
{
    /**
     * @var string
     */
    static $registerUrl = 'https://%s.joinposter.com/api/v2/auth/access_token';

    /**
     * @var string
     */
    static $storagePostfix = 'storage.getStorages';

    /**
     * @var string
     */
    static $ingredientsPostfix = 'menu.getIngredients';

    /**
     * @var string
     */
    static $productsPostfix = 'menu.getProducts';

    /**
     * @var string
     */
    static $agentsPostfix = 'storage.getSuppliers';

    /**
     * @var string
     */
    static $waybillPostfix = 'storage.createSupply';

    /**
     * @var
     */
    private $accessToken;

    /**
     * @var
     */
    private static $instance = null;

    /**
     * @var
     */
    private $orgId;

    /**
     * Poster constructor.
     *
     * @param $orgId
     */
    public function __construct($orgId)
    {
        $this->orgId = $orgId;
        $this->accessToken = ISV::getSettingsByServiceId(Registry::POSTER_SERVICE_ID, $this->orgId, ['access_token']);
    }

    /**
     * @param $orgId
     * @return Poster
     */
    public static function getInstance($orgId)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($orgId);
        }

        return self::$instance;
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function generateAuthUrl($request)
    {
        $appId = \Yii::$app->params['posterAppId'];
        if (empty($appId)) {
            throw new BadRequestHttpException('poster.not_set_app_id');
        }
        $redirectUrl = $request['redirect_url'] ?? $_SERVER['HTTP_ORIGIN'] . '/poster-auth';

        return ['result' => \Yii::$app->params['posterApiUrl'] . "auth?application_id=$appId&redirect_uri=$redirectUrl&response_type=code"];
    }

    /**
     * @param $request
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function saveAccessKey($request)
    {
        $url = sprintf(Poster::$registerUrl, $request['account']);
        $auth = [
            'application_id'     => \Yii::$app->params['posterAppId'],
            'application_secret' => \Yii::$app->params['posterAppSecretKey'],
            'grant_type'         => 'authorization_code',
            'redirect_uri'       => $request['url'],
            'code'               => $request['code'],
        ];
        $data = $this->sendRequest($url, 'post', $auth);
        if (isset($data['code']) && $data['code'] >= 400) {
            throw new BadRequestHttpException($data['error_message']);
        }
        $accessTokenSetting = IntegrationSetting::findOne(['name' => 'access_token', 'service_id' => Registry::POSTER_SERVICE_ID]);
        $setting = ISV::findOne(['org_id' => $this->orgId, 'setting_id' => $accessTokenSetting->id]);
        if (!$setting) {
            $setting = new ISV();
            $setting->org_id = $this->orgId;
            $setting->setting_id = $accessTokenSetting->id;
        }
        $setting->value = $data['access_token'];
        $success = $setting->save();

        return ['result' => $success];
    }

    /**
     * @param        $url
     * @param string $type
     * @param array  $params
     * @param bool   $json
     * @return bool|string
     */
    public function sendRequest($url, $type = 'get', $params = [], $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($type == 'post' || $type == 'put') {
            curl_setopt($ch, CURLOPT_POST, true);

            if ($json) {
                $params = json_encode($params);

                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($params)
                ]);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Poster (http://joinposter.com)');

        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data, true);
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function getStores()
    {
        $url = $this->generateRequestUrl(self::$storagePostfix);
        $response = $this->sendRequest($url);
        array_unshift($response['response'], ['storage_id' => md5(self::$storagePostfix), 'storage_name' => 'Все склады', 'type' => 'rootnode', 'delete' => 0]);
        foreach ($response['response'] as $store) {
            $model = OuterStore::findOne(['outer_uid' => $store['storage_id'], 'org_id' => $this->orgId, 'service_id' => Registry::POSTER_SERVICE_ID]);
            if (!$model) {
                $model = new OuterStore([
                    'outer_uid'  => $store['storage_id'],
                    'service_id' => Registry::POSTER_SERVICE_ID,
                    'org_id'     => $this->orgId,
                    'name'       => $store['storage_name'],
                ]);
                if (isset($store['type']) && $store['type'] == 'rootnode') {
                    $model->makeRoot();
                    $rootNode = $model;
                } else {
                    /** @var OuterStore $rootNode */
                    $model->prependTo($rootNode);
                }
            }
            $model->is_deleted = (int)$store['delete'];
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function getProducts()
    {
        $url = $this->generateRequestUrl(self::$ingredientsPostfix);
        $response = $this->sendRequest($url);

        foreach ($response['response'] as $ingredient) {
            $model = OuterProduct::findOne(['outer_uid' => $ingredient['ingredient_id'], 'org_id' => $this->orgId, 'service_id' => Registry::POSTER_SERVICE_ID]);
            if (!$model) {
                $model = new OuterProduct([
                    'outer_uid'             => $ingredient['ingredient_id'],
                    'service_id'            => Registry::POSTER_SERVICE_ID,
                    'org_id'                => $this->orgId,
                    'name'                  => $ingredient['ingredient_name'],
                    'is_deleted'            => 0,
                    'outer_product_type_id' => 4,
                ]);
            }

            if (!empty($ingredient['ingredient_unit'])) {
                $obUnitModel = OuterUnit::findOne([
                    'name'       => $ingredient['ingredient_unit'],
                    'service_id' => Registry::POSTER_SERVICE_ID,
                    'org_id'     => $this->orgId
                ]);

                if (!$obUnitModel) {
                    $obUnitModel = new OuterUnit();
                    $obUnitModel->name = $ingredient['ingredient_unit'];
                    $obUnitModel->service_id = Registry::POSTER_SERVICE_ID;
                    $obUnitModel->org_id = $this->orgId;
                    $obUnitModel->outer_uid = md5($ingredient['ingredient_unit']);
                } else {
                    $obUnitModel->updated_at = \gmdate('Y-m-d H:i:s');
                }

                $obUnitModel->is_deleted = 0;
                $obUnitModel->save();

                $model->outer_unit_id = $obUnitModel->id;
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

        $url = $this->generateRequestUrl(self::$productsPostfix);
        $response = $this->sendRequest($url);

        foreach ($response['response'] as $ingredient) {
            $model = OuterProduct::findOne(['outer_uid' => $ingredient['product_id'], 'org_id' => $this->orgId, 'service_id' => Registry::POSTER_SERVICE_ID]);
            if (!$model) {
                $model = new OuterProduct([
                    'outer_uid'             => $ingredient['product_id'],
                    'service_id'            => Registry::POSTER_SERVICE_ID,
                    'org_id'                => $this->orgId,
                    'name'                  => $ingredient['product_name'],
                    'is_deleted'            => 0,
                    'outer_product_type_id' => 1,
                ]);
            }

            if (!empty($ingredient['unit'])) {
                $obUnitModel = OuterUnit::findOne([
                    'name'       => $ingredient['unit'],
                    'service_id' => Registry::POSTER_SERVICE_ID,
                    'org_id'     => $this->orgId
                ]);

                if (!$obUnitModel) {
                    $obUnitModel = new OuterUnit();
                    $obUnitModel->name = $ingredient['unit'];
                    $obUnitModel->service_id = Registry::POSTER_SERVICE_ID;
                    $obUnitModel->org_id = $this->orgId;
                    $obUnitModel->outer_uid = md5($ingredient['unit']);
                } else {
                    $obUnitModel->updated_at = \gmdate('Y-m-d H:i:s');
                }

                $obUnitModel->is_deleted = 0;
                $obUnitModel->save();

                $model->outer_unit_id = $obUnitModel->id;
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    public function getAgents()
    {
        $url = $this->generateRequestUrl(self::$agentsPostfix);
        $response = $this->sendRequest($url);

        foreach ($response['response'] as $agent) {
            $model = OuterAgent::findOne(['outer_uid' => $agent['supplier_id'], 'org_id' => $this->orgId, 'service_id' => Registry::POSTER_SERVICE_ID]);
            if (!$model) {
                $model = new OuterAgent([
                    'outer_uid'  => $agent['supplier_id'],
                    'service_id' => Registry::POSTER_SERVICE_ID,
                    'org_id'     => $this->orgId,
                    'name'       => $agent['supplier_name'],
                    'is_deleted' => $agent['delete'],
                    'inn'        => $agent['supplier_tin'],
                ]);
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

        return true;
    }

    /**
     * @param $postfix
     * @return string
     */
    private function generateRequestUrl($postfix)
    {
        return \Yii::$app->params['posterApiUrl'] . $postfix . '?token=' . $this->accessToken;
    }

    /**
     * @param Waybill $waybill
     * @return bool
     */
    public function sendWaybill(Waybill $waybill)
    {
        $url = $this->generateRequestUrl(self::$waybillPostfix);
        $supply = [
            "supply" => [
                "date"        => date("Y-m-d H:i:s"),
                "supplier_id" => $waybill->outerAgent->outer_uid,
                "storage_id"  => $waybill->outerStore->outer_uid,
            ]
        ];
        $arIngredients = [];
        foreach ($waybill->waybillContents as $item) {
            $arIngredients[] = [
                'id'   => $item->productOuter->outer_uid,
                'type' => $item->productOuter->outerProductType->value,
                'num'  => $item->quantity_waybill * $item->koef,
                'sum'  => $item->price_with_vat,
            ];
        }

        $supply['ingredient'] = $arIngredients;
        $response = $this->sendRequest($url, 'post', $supply);

        return (bool)$response['success'];
    }
}