<?php

namespace frontend\modules\clientintegr\modules\iiko\helpers;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoWaybill;
use yii\helpers\ArrayHelper;

class iikoApi
{
    private $host = 'http://192.168.100.39:8080/resto/api';
    private $login;
    private $pass;
    private $token;

    var $response = '';

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
            self::$_instance->host = iikoDicconst::getSetting('URL');
            self::$_instance->login = iikoDicconst::getSetting('auth_login');
            self::$_instance->pass = iikoDicconst::getSetting('auth_password');
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
     * @param $login
     * @param $password
     * @return bool
     */
    public function auth($login = null, $password = null)
    {
        if(is_null($login)) {
            $login = $this->login;
        }

        if(is_null($password)) {
            $password = $this->pass;
        }

        $params = [
            'login' => $login,
            'pass' => hash('sha1', $password)
        ];

        if($this->token = $this->sendAuth('/auth', $params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Выход с апи
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
                unset($item['id']);
                if (isset($item['productGroupType'])) {
                    $categories[$id] = $item;
                } else {
                    $products[$id] = $item;
                    $units[] = $item['mainUnit'];
                }
            }
        }

        return [
            'categories' => $categories,
            'products' => $products,
            'units' => array_unique($units)
        ];
    }


    /**
     * Список складов
     * @return mixed
     */
    public function getStores() {
        return self::xmlToArray($this->send('/corporation/stores/'));
    }

    /**
     * Список контрагентов
     * @return mixed
     */
    public function getSuppliers() {
        return self::xmlToArray($this->send('/suppliers/'));
    }

    /**
     * @param iikoWaybill $model
     * @return mixed
     */
    public function sendWaybill(iikoWaybill $model) {
        $url = '/documents/import/incomingInvoice';
        return $this->sendXml($url, $model->getXmlDocument());
    }

    /**
     * Обычный SEND без чанков. Копия обычной SEND() для авторизации,
     * так как авторизация не поддерживает запрос только с HEADERS для определения чанков
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     */
    private function sendAuth($url, $params = [], $method = 'GET', $headers = [])
    {
        $header = ['Content-Type: application/x-www-form-urlencoded'];
        $header = ArrayHelper::merge($header, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        /**
         * Logger
         */
        if(isset(\Yii::$app->params['iikoLogOrganization'])) {
            $org_id  = \Yii::$app->user->identity->organization_id;
            if(in_array($org_id, \Yii::$app->params['iikoLogOrganization'])){
                $file = \Yii::$app->basePath . '/runtime/logs/iiko_api_response_'. $org_id .'.log';
                $message = [
                    '(AUTH PROCEDURE!)',
                    'DATE: ' . date('d.m.Y H:i:s'),
                    'URL: ' . $url,
                    'HTTP_CODE: ' . $info['http_code'],
                    'LENGTH: '. $info['download_content_length'],
                    'SIZE_DOWNLOAD: '. $info['size_download'],
                    'HTTP_URL: ' . $info['url'],
                    'RESPONSE: ' . $response,
                    'RESP_SIZE:' . sizeof($response),
                    'KEY: ' . $this->token,
                    str_pad('', 200, '-') . PHP_EOL
                ];
                file_put_contents($file, implode(PHP_EOL, $message), FILE_APPEND);
                file_put_contents($file, print_r($response,true).PHP_EOL, FILE_APPEND);
                file_put_contents($file, print_r($info,true).PHP_EOL, FILE_APPEND);

            }
        }

        if($info['http_code'] != 200) {
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . ' | ');
        }

        return $response;
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     */
    private function send($url, $params = [], $method = 'GET', $headers = [])
    {
        $header = ['Content-Type: application/x-www-form-urlencoded'];
        $header = ArrayHelper::merge($header, $headers);

        $chunked = false; // Признак разбиения BODY на chunked куски
        $response = &$this -> response;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $headerArray = self::getHeadersCurl($response);

        if ($headerArray['Transfer-Encoding'] == 'chunked')
            $chunked = true;

        /**
         * Chunked Logger
         */
        if(isset(\Yii::$app->params['iikoLogOrganization'])) {
            $org_id  = \Yii::$app->user->identity->organization_id;
            if(in_array($org_id, \Yii::$app->params['iikoLogOrganization'])){
                $file = \Yii::$app->basePath . '/runtime/logs/iiko_api_response_'. $org_id .'.log';
                $message = [
                    '(Chunked mode detection...)',
                    'DATE: ' . date('d.m.Y H:i:s'),
                    'URL: ' . $url,
                    'HTTP_CODE: ' . $info['http_code'],
                    'LENGTH: '. $info['download_content_length'],
                    'SIZE_DOWNLOAD: '. $info['size_download'],
                    'HTTP_URL: ' . $info['url'],
                    'RESPONSE: ' . $response,
                    'RESP_SIZE:' . sizeof($response),
                    'KEY: ' . $this->token,
                    'CHUNKED MODE DETECTED :' . $chunked,
                    str_pad('', 200, '-') . PHP_EOL
                ];
                file_put_contents($file, implode(PHP_EOL, $message), FILE_APPEND);
            }
        }

        if($info['http_code'] != 200) {
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . ' | ');
        }

        unset($info);
        curl_close($ch);

        // Start real request with BODY onboard

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, implode(PHP_EOL, $header));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($chunked) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'Callback'));
            curl_exec($ch);
        } else {
            $response = curl_exec($ch);
        }

        $info = curl_getinfo($ch);

        curl_close($ch);

        /**
         * Logger
         */

        if(isset(\Yii::$app->params['iikoLogOrganization'])) {
            $org_id  = \Yii::$app->user->identity->organization_id;
            if(in_array($org_id, \Yii::$app->params['iikoLogOrganization'])){
                $file = \Yii::$app->basePath . '/runtime/logs/iiko_api_response_'. $org_id .'.log';
                $message = [
                    '(Normal request)',
                    'DATE: ' . date('d.m.Y H:i:s'),
                    'URL: ' . $url,
                    'HTTP_CODE: ' . $info['http_code'],
                    'LENGTH: '. $info['download_content_length'],
                    'SIZE_DOWNLOAD: '. $info['size_download'],
                    'HTTP_URL: ' . $info['url'],
                    'RESP_SIZE:' . sizeof($response),
                    'KEY: ' . $this->token,
                    str_pad('', 200, '-') . PHP_EOL
                ];
                file_put_contents($file, implode(PHP_EOL, $message), FILE_APPEND);
                file_put_contents($file, print_r($response,true).PHP_EOL, FILE_APPEND);

            }
        }


        if($info['http_code'] != 200) {
            throw new \Exception('Код ответа сервера: ' . $info['http_code'] . ' | ');
        }



        return $response;
    }

    /**
     * @param $ch
     * @param $str
     * @return int
     */

    function Callback($ch, $str){
       $response = &$this -> response;
       $response .= $str;

        return strlen($str);
    }

    /**
     * @param $url
     * @param $body
     * @param array $headers
     * @return mixed
     */
    private function sendXml($url, $body, $headers = [])
    {
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_COOKIE, 'key=' . $this->token);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);


        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $info1 = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        if($info['http_code'] !== 200) {
            print_r($response);
            //print_r(['r' => $response,'header' => $header, 'info' => $info1]);
            //\Yii::info('error: ' . print_r($info, 1), 'iiko_api');
            return false;
        }

        return $response;
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

    public static function getHeadersCurl($response){
        $headers = array();
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line){
            if ($i === 0)
                $headers['http_code'] = $line;
            else{
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}