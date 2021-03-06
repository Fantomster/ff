<?php

namespace frontend\modules\clientintegr\modules\iiko\helpers;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoWaybill;
use api_web\modules\integration\modules\iiko\helpers\iikoLogger;
use common\models\Waybill;
use yii\helpers\ArrayHelper;

class iikoApi
{
    private $host = 'http://192.168.100.39:8080/resto/api';
    private $login;
    private $pass;
    private $token;

    var $response = '';

    protected static $_instance;

    public static function getInstance($orgId = null)
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
            self::$_instance->host = iikoDicconst::getSetting('URL_iiko', $orgId);
            self::$_instance->login = iikoDicconst::getSetting('auth_login', $orgId);
            self::$_instance->pass = iikoDicconst::getSetting('auth_password', $orgId);
        }

        return self::$_instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Авторизация
     *
     * @param $login
     * @param $password
     * @return bool
     */
    public function auth($login = null, $password = null)
    {
        if (is_null($login)) {
            $login = $this->login;
        }

        if (is_null($password)) {
            $password = $this->pass;
        }

        $params = [
            'login' => $login,
            'pass'  => hash('sha1', $password)
        ];

        if ($this->token = $this->sendAuth('/auth', $params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Выход с апи
     *
     * @return mixed
     */
    public function logout()
    {
        if (!empty($this->token)) {
            $this->sendAuth('/logout');
        }
    }

    /**
     * Список категорий и продуктов
     *
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
     * @return mixed
     */
    public function getStores()
    {
        return self::xmlToArray($this->send('/corporation/stores/'));
    }

    /**
     * Список контрагентов
     *
     * @return mixed
     */
    public function getSuppliers()
    {
        return self::xmlToArray($this->send('/suppliers/'));
    }

    /**
     * @param iikoWaybill|Waybill $model
     * @return mixed
     */
    public function sendWaybill($model)
    {
        if ($model instanceof iikoWaybill || $model instanceof \api_web\modules\integration\models\iikoWaybill) {
            $url = '/documents/import/incomingInvoice';
            return $this->sendXml($url, $model->getXmlDocument());
        }
        return false;
    }

    /**
     * Обычный SEND без чанков. Копия обычной SEND() для авторизации,
     * так как авторизация не поддерживает запрос только с HEADERS для определения чанков
     *
     * @param        $url
     * @param array  $params
     * @param string $method
     * @param array  $headers
     * @return mixed
     * @throws \Exception
     */
    private function sendAuth($url, $params = [], $method = 'GET', $headers = [])
    {
        $logger = new iikoLogger();
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] != 200) {
            $logger->setType('error');
            $logger->response(['info' => $info, 'response' => $response]);
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . PHP_EOL . curl_error($ch));
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
        $logger = new iikoLogger();
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
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . ' | ' . curl_error($ch));
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
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . ' | ' . curl_error($ch));
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
     * @return mixed
     */
    private function sendXml($url, $body, $headers = [])
    {
        $logger = new iikoLogger();
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
}
