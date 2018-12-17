<?php

namespace api_web\components;

use Yii;
use yii\web\HttpException;

class FireBase
{
    function __wakeup()
    {
    }

    function __clone()
    {
    }

    function __construct()
    {
    }

    /**
     * @var FireBase
     */
    private static $instance;

    /**
     * @var \api_web\components\FireBaseLib
     */
    private static $fireBase;

    /**
     * @var string
     */
    private static $path;

    /**
     * @return FireBase
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    /**
     *
     */
    public static function unsetInstance()
    {
        if (self::$instance != null) {
            self::$instance = null;
        }
    }

    /**
     * @return FireBase
     */
    private static function createInstance()
    {
        self::$path = Yii::$app->params['fireBase']['DEFAULT_PATH'] ?? '/';
        $url = Yii::$app->params['fireBase']['DEFAULT_URL'];
        $token = Yii::$app->params['fireBase']['DEFAULT_TOKEN'];
        self::$fireBase = new \api_web\components\FireBaseLib($url, $token);
        return new self();
    }

    /**
     * @param $path
     * @return string
     */
    private function getPath($path)
    {
        $pathReturn = self::$path;
        foreach ($path as $key => $value) {
            if ($key === 0 || $key === '0') {
                $key = $value;
                $value = null;
            }
            $pathReturn .= '/' . $key . '/' . ($value ?? '');
        }
        $pathReturn = rtrim(str_replace('//', '/', $pathReturn), '/');
        return $pathReturn;
    }

    /**
     * @param $path
     * @return array
     */
    public function get($path)
    {
        $path = $this->getPath($path);
        return self::$fireBase->get($path);
    }

    /**
     * @param $path
     * @param $data
     * @throws HttpException
     */
    public function set($path, $data)
    {
        $path = $this->getPath($path);
        $result = self::$fireBase->set($path, $data);
        if (strstr($result, 'error') !== false) {
            throw new HttpException(401, $result, 401);
        }
    }

    /**
     * @param $path
     * @param $data
     * @return array
     */
    public function update($path, $data)
    {
        return self::$fireBase->update($this->getPath($path), $data);
    }

    /**
     * @param $path
     * @throws HttpException
     */
    public function delete($path)
    {
        $path = $this->getPath($path);
        $result = self::$fireBase->delete($path);
        if (strstr($result, 'error') !== false) {
            throw new HttpException(401, $result, 401);
        }
    }
}