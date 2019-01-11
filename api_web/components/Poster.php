<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-10
 * Time: 12:45
 */

namespace api_web\components;

use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue as ISV;

/**
 * Class Poster
 *
 * @package api_web\components
 */
class Poster extends WebApi
{
    /**
     * @var string
     */
    static $registerUrl = 'https://%s.joinposter.com/api/v2/auth/access_token';

    /**
     * @param $request
     * @return array
     */
    public function generateAuthUrl($request)
    {
        $appId = ISV::getSettingsByServiceId(Registry::POSTER_SERVICE_ID, $this->user->organization_id, ['application_id']);
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
        $this->validateRequest($request, ['account', 'url', 'code']);
        $url = sprintf(Poster::$registerUrl, $request['account']);
        $posterSettings = ISV::getSettingsByServiceId(Registry::POSTER_SERVICE_ID, $this->user->organization_id);
        $auth = [
            'application_id'     => $posterSettings['application_id'],
            'application_secret' => $posterSettings['application_secret'],
            'grant_type'         => 'authorization_code',
            'redirect_uri'       => $request['url'],
            'code'               => $request['code'],
        ];
        $data = json_decode($this->sendRequest($url, 'post', $auth));
        if (isset($data->code) && $data->code >= 400) {
            return ['result' => false, 'error' => $data->error_message];
        }
        $accessTokenSetting = IntegrationSetting::findOne(['name' => 'access_token', 'service_id' => Registry::POSTER_SERVICE_ID]);
        $setting = ISV::findOne(['org_id' => $this->user->organization_id, 'setting_id' => $accessTokenSetting->id]);
        if (!$setting) {
            $setting = new ISV();
            $setting->org_id = $this->user->organization_id;
            $setting->setting_id = $accessTokenSetting->id;
        }
        $setting->value = $data->access_token;
        $success = $setting->save();

        return ['result' => $success, 'data_from_server' => $data];
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

        return $data;
    }
}