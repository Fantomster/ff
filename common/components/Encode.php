<?php

namespace common\components;

use yii\base\Component;

class Encode extends Component
{
    protected $key;

    public function __construct(array $config = [])
    {
        $this->key = \Yii::$app->params['encrypt']['salt'];
        parent::__construct($config);
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public function encrypt(string $data, string $key): string
    {
        return base64_encode(\Yii::$app->security->encryptByKey($data, $key)) ?: false;
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public function decrypt(string $data, string $key): string
    {
        return \Yii::$app->security->decryptByKey(base64_decode($data), $key) ?: false;
    }

    /**
     * @param string $salt
     * @return string
     */
    protected function getKeyEncode(string $salt): string
    {
        return md5("{$this->key}{$salt}");
    }
}