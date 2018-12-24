<?php

namespace api_web\components;

use Firebase\FirebaseInterface;

class FireBaseLib implements FirebaseInterface
{
    private $_baseURI;
    private $_timeout;
    private $_token;
    private $_curlHandler = null;

    /**
     * Constructor
     *
     * @param string $baseURI
     * @param string $token
     */
    function __construct($baseURI = '', $token = '')
    {
        if ($baseURI == '') {
            trigger_error('You must provide a baseURI variable.', E_USER_ERROR);
        }

        if (!extension_loaded('curl')) {
            trigger_error('Extension CURL is not loaded.', E_USER_ERROR);
        }

        $this->setBaseURI($baseURI);
        $this->setTimeOut(1);
        $this->setToken($token);
        $this->initCurlHandler();
    }

    /**
     * Initializing the CURL handler
     *
     * @return void
     */
    public function initCurlHandler()
    {
        $this->_curlHandler = curl_init();
    }

    /**
     * Closing the CURL handler
     *
     * @return void
     */
    public function closeCurlHandler()
    {
        curl_close($this->_curlHandler);
    }

    /**
     * Sets Token
     *
     * @param string $token Token
     * @return void
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * Sets Base URI, ex: http://yourcompany.firebase.com/youruser
     *
     * @param string $baseURI Base URI
     * @return void
     */
    public function setBaseURI($baseURI)
    {
        $baseURI .= (substr($baseURI, -1) == '/' ? '' : '/');
        $this->_baseURI = $baseURI;
    }

    /**
     * Returns with the normalized JSON absolute path
     *
     * @param  string $path    Path
     * @param  array  $options Options
     * @return string
     */
    private function _getJsonPath($path, $options = [])
    {
        $url = $this->_baseURI;
        if ($this->_token !== '') {
            $options['auth'] = $this->_token;
        }
        $path = ltrim($path, '/');
        return $url . $path . '.json?' . http_build_query($options);
    }

    /**
     * Sets REST call timeout in seconds
     *
     * @param integer $seconds Seconds to timeout
     * @return void
     */
    public function setTimeOut($seconds)
    {
        $this->_timeout = $seconds;
    }

    /**
     * Writing data into Firebase with a PUT request
     * HTTP 200: Ok
     *
     * @param string $path    Path
     * @param mixed  $data    Data
     * @param array  $options Options
     * @return array Response
     */
    public function set($path, $data, array $options = [])
    {
        return $this->_writeData($path, $data, 'PUT', $options);
    }

    /**
     * Pushing data into Firebase with a POST request
     * HTTP 200: Ok
     *
     * @param string $path    Path
     * @param mixed  $data    Data
     * @param array  $options Options
     * @return array Response
     */
    public function push($path, $data, array $options = [])
    {
        return $this->_writeData($path, $data, 'POST', $options);
    }

    /**
     * Updating data into Firebase with a PATH request
     * HTTP 200: Ok
     *
     * @param string $path    Path
     * @param mixed  $data    Data
     * @param array  $options Options
     * @return array Response
     */
    public function update($path, $data, array $options = [])
    {
        if (!$this->_curlHandler) {
            $this->initCurlHandler();
        }

        $jsonData = json_encode($data);
        $header = [
            'Content-Type: application/json'
        ];
        try {
            $ch = $this->_getCurlHandler($path, 'PATCH', $options);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            $return = curl_exec($ch);
            $this->closeCurlHandler();
        } catch (\Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Reading data from Firebase
     * HTTP 200: Ok
     *
     * @param string $path    Path
     * @param array  $options Options
     * @return array Response
     */
    public function get($path, array $options = [])
    {
        if (!$this->_curlHandler) {
            $this->initCurlHandler();
        }

        try {
            $ch = $this->_getCurlHandler($path, 'GET', $options);
            $return = curl_exec($ch);
            $this->closeCurlHandler();
        } catch (\Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Deletes data from Firebase
     * HTTP 204: Ok
     *
     * @param string $path    Path
     * @param array  $options Options
     * @return array Response
     */
    public function delete($path, array $options = [])
    {
        try {
            $ch = $this->_getCurlHandler($path, 'DELETE', $options);
            $return = curl_exec($ch);
        } catch (\Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Returns with Initialized CURL Handler
     *
     * @param string $path    Path
     * @param string $mode    Mode
     * @param array  $options Options
     * @return resource Curl Handler
     */
    private function _getCurlHandler($path, $mode, $options = [])
    {
        $url = $this->_getJsonPath($path, $options);
        $ch = $this->_curlHandler;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'web_api');
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 1200);
        return $ch;
    }

    private function _writeData($path, $data, $method = 'PUT', $options = [])
    {
        if (!$this->_curlHandler) {
            $this->initCurlHandler();
        }

        $jsonData = json_encode($data);
        $header = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Connection: Close'
        ];
        try {
            $ch = $this->_getCurlHandler($path, $method, $options);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $return = curl_exec($ch);
            $this->closeCurlHandler();
        } catch (\Exception $e) {
            $return = null;
        }
        return $return;
    }
}