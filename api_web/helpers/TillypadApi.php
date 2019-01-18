<?php

namespace api_web\helpers;

use api_web\components\Registry;
use api_web\modules\integration\models\TillypadWaybill;
use common\models\IntegrationSettingValue;
use common\models\Waybill;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class TillypadApi
 *
 * @package api_web\helpers
 * @var $host      string Адрес Tillypad
 * @var $login     string Логин
 * @var $password  string Пароль
 * @var $token     string Токен
 * @var $response  string Ответ запроса
 * @var $_instance TillypadApi Объект класса
 */
class TillypadApi
{
    private $host;
    private $login;
    private $password;
    private $token;
    private $orgId;

    public $response;

    protected static $_instance;

    /**
     * @param int|null $orgId
     * @return TillypadApi
     */
    public static function getInstance(int $orgId = null): self
    {
        if (self::$_instance === null) {
            $iSettingVal = IntegrationSettingValue::getSettingsByServiceId(Registry::TILLYPAD_SERVICE_ID, $orgId, [
                'URL',
                'auth_login',
                'auth_password'
            ]);
            self::$_instance = new self;
            self::$_instance->host = $iSettingVal['URL'];
            self::$_instance->login = $iSettingVal['auth_login'];
            self::$_instance->password = $iSettingVal['auth_password'];
            self::$_instance->orgId = $orgId;
        }

        return self::$_instance;
    }

    /**
     * @param string $name
     * @param string $value
     * @throws BadRequestHttpException
     */
    public function setAttribute(string $name, string $value): void
    {
        if (property_exists(self::$_instance, $name)) {
            self::$_instance->{$name} = $value;
        } else {
            throw new BadRequestHttpException("Tillypad attribute not found {$name}");
        }
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Авторизация
     *
     * @param string $login
     * @param string $password
     * @param int    $timeout
     * @return bool
     * @throws \Exception
     */
    public function auth(string $login = null, string $password = null, int $timeout = null): bool
    {
        $login = is_null($login) ? $this->login : $login;
        $password = is_null($password) ? $this->password : $password;

        $params = [
            'login' => $login,
            'pass'  => hash('sha1', $password)
        ];

        try {
            $this->token = $this->sendAuth('/auth', $params, 'GET', [], $timeout);

            return $this->token ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Выход с апи
     *
     * @throws \Exception
     */
    public function logout(): void
    {
        if (!empty($this->token)) {
            try {
                $this->sendAuth('/logout');
                $this->token = null;
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Список категорий и продуктов
     *
     * @throws \Exception
     * @return array
     */
    public function getProducts()
    {
        $products = [];
        $categories = [];
        $units = [];
        $result = self::xmlToArray($this->send('/products/'));
        if (!empty($result) && isset($result['productDto'])) {
            foreach ($result['productDto'] as $item) {
                $id = $item['id'];
                if (isset($item['productGroupType'])) {
                    $categories[$id] = $item;
                } else {
                    if ($item['productType'] == 'GOODS') {
                        $products[$id] = $item;
                        $units[] = $item['mainUnit'];
                    }
                }
            }
        }

        $result = null;

        return [
            'categories' => $categories,
            'products'   => $products,
            'units'      => array_unique($units)
        ];
    }

    /**
     * Список складов
     *
     * @throws \Exception
     * @return mixed
     */
    public function getStores()
    {
        return self::xmlToArray($this->send('/corporation/stores/'));
    }

    /**
     * Список контрагентов
     *
     * @throws \Exception
     * @return mixed
     */
    public function getSuppliers()
    {
        return self::xmlToArray($this->send('/suppliers/'));
    }

    /**
     * @param TillypadWaybill|Waybill $model
     * @throws \Exception
     * @return mixed
     */
    public function sendWaybill($model)
    {
        if ($model instanceof TillypadWaybill) {
            $url = '/documents/import/incomingInvoice';

            return $this->sendXml($url, $model->getXmlDocument());
        }

        return false;
    }

    /**
     * @param        $url
     * @param array  $params
     * @param string $method
     * @param array  $headers
     * @param int    $timeout
     * @return bool|string
     * @throws \Exception
     */
    private function sendAuth($url, $params = [], $method = 'GET', $headers = [], $timeout = 300)
    {
        $logger = new TillypadLogger();
        $logger->setOperation($url);
        $logger->request($params);

        $header = ['Content-Type: application/x-www-form-urlencoded'];
        $header = ArrayHelper::merge($header, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] != 200) {
            $logger->setType('error');
            $logger->response(['info' => $info, 'response' => $response]);
            throw new \Exception(\Yii::t('api_web', "Server response:{code}|{text}", ['ru' => 'Код ответа сервера:{code}|{text}', 'code' => $info['http_code'], 'text' => curl_error($ch)]));
        }
        $logger->response($response);

        return $response;
    }

    /**
     * @param        $url
     * @param array  $params
     * @param string $method
     * @param array  $headers
     * @return mixed|string
     * @throws \Exception
     */
    private function send($url, $params = [], $method = 'GET', $headers = [])
    {
        $logger = new TillypadLogger();
        $logger->setOperation($url);
        $logger->request($params);

        $header = ['Content-Type: application/x-www-form-urlencoded'];
        $header = ArrayHelper::merge($header, $headers);

        $chunked = false; // Признак разбиения BODY на chunked куски
        $response = &$this->response;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $headerArray = self::getHeadersCurl($response);

        if (array_key_exists('Transfer-Encoding', $headerArray)) {
            if ($headerArray['Transfer-Encoding'] == 'chunked')
                $chunked = true;
        }

        if ($info['http_code'] != 200) {
            $logger->setType('error');
            $logger->response(['info' => $info, 'response' => $response]);
            throw new \Exception(\Yii::t('api_web', "Server response:{code}|{text}", ['ru' => 'Код ответа сервера:{code}|{text}', 'code' => $info['http_code'], 'text' => curl_error($ch)]));
        }

        $response = '';
        unset($info);
        curl_close($ch);

        // Start real request with BODY onboard
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($chunked) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this, 'Callback']);
            curl_exec($ch);
        } else {
            $response = curl_exec($ch);
        }

        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] != 200) {
            $logger->setType('error');
            $logger->response(['info' => $info, 'response' => $response]);
            throw new \Exception(\Yii::t('api_web', "Server response:{code}|{text}", ['ru' => 'Код ответа сервера:{code}|{text}', 'code' => $info['http_code'], 'text' => curl_error($ch)]));
        }

        $logger->response($response);

        return $response;
    }

    /**
     * @param $ch
     * @param $str
     * @return int
     */

    function Callback($ch, $str)
    {
        $response = &$this->response;
        $response .= $str;

        return strlen($str);
    }

    /**
     * @param       $url
     * @param       $body
     * @param array $headers
     * @throws \Exception
     * @return mixed
     */
    private function sendXml($url, $body, $headers = [])
    {
        $logger = new TillypadLogger();
        $logger->setOperation($url);
        $logger->request($body);

        $header = [
            "Content-type: application/xml",
            "Content-length: " . strlen($body),
            "Connection: close"
        ];

        $header = ArrayHelper::merge($header, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($info['http_code'] !== 200) {
            $logger->setType('error');
            $logger->response(['info' => $info, 'response' => $response]);

            return $response;
        }

        $logger->response($response);
        return true;
    }

    /**
     * @param $xml
     * @return mixed
     */
    public static function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml)), true);
    }

    /**
     * @param $response
     * @return array
     */

    public static function getHeadersCurl($response)
    {
        $headers = [];
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    /**
     * Количество свободных лицензий на iikoServer
     *
     * @return bool|string
     */
    public function getLicenseCount()
    {
        $url = $this->host . "/licence/info?moduleId=2000";
        return file_get_contents($url);
    }
}
