<?php

namespace api_web\components;

use Yii;
use \Firebase\FirebaseLib;
use yii\web\HttpException;

class FireBase
{

    function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    function __clone()
    {
        // TODO: Implement __clone() method.
    }

    function __construct()
    {
    }

    /**
     * @var FireBase
     */
    private static $instance;

    /**
     * @var FirebaseLib
     */
    private static $fireBase;

    /**
     * @var string
     */
    private static $path;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    private static function createInstance()
    {
        self::$path = Yii::$app->params['fireBase']['DEFAULT_PATH'] ?? '/';
        $url = Yii::$app->params['fireBase']['DEFAULT_URL'];
        $token = Yii::$app->params['fireBase']['DEFAULT_TOKEN'];
        self::$fireBase = new FirebaseLib($url, $token);
        return new self();
    }

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

    public function get($path)
    {
        $path = $this->getPath($path);
        return self::$fireBase->get($path);
    }

    public function set($path, $data)
    {
        $path = $this->getPath($path);
        $result = self::$fireBase->set($path, $data);
        if (strstr($result, 'error') !== false) {
            throw new HttpException(401, $result, 401);
        }
    }

    public function update($path, $data)
    {
        $path = $this->getPath($path);
        return self::$fireBase->update($path, $data);
    }

    public function delete($path)
    {
        $path = $this->getPath($path);
        $result = self::$fireBase->delete($path);
        if (strstr($result, 'error') !== false) {
            throw new HttpException(401, $result, 401);
        }
    }
}