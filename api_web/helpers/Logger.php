<?php

namespace api_web\helpers;

use Yii;
use yii\db\Expression;
use common\models\User;
use yii\db\Query;

class Logger
{
    private static $tableName = 'web_api_log';

    private static $guide;
    private static $instance;

    function __clone()
    {
    }

    function __wakeup()
    {
    }

    function __construct()
    {
        if (Yii::$app->params['web_api_log'] == true) {
            self::$guide = md5(uniqid(microtime(), 1));
            self::insert([
                'guide' => self::$guide,
                'ip' => Yii::$app->request->getUserIP(),
                'url' => Yii::$app->request->getUrl(),
            ]);
        }
    }

    /**
     * При создании экзэмпляра сразу создаем запись с уникальным guide
     * далее все методы будут работать только с этим guide
     * З.Ы. обычный синглтон
     * @return Logger
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $request
     * @throws \Exception
     */
    public static function request($request)
    {
        $serializedRequest = \json_encode($request, JSON_UNESCAPED_UNICODE);
        if (mb_strlen($serializedRequest) > 65535){
            $serializedRequest = mb_substr($serializedRequest, 0, 65534);
        }
        if (empty(self::get()['request_at']) || self::get()['request_at'] == '0000-00-00 00:00:00') {
            self::update([
                'request' => $serializedRequest,
                'request_at' => new Expression('NOW()')
            ]);
        }
    }

    /**
     * @param $response
     * @throws \Exception
     */
    public static function response($response)
    {
        if (empty(self::get()['response_at']) || self::get()['response_at'] == '0000-00-00 00:00:00' || self::get()['response_at'] == null) {
            self::update([
                'response' => mb_substr(\json_encode($response, JSON_UNESCAPED_UNICODE), 0, 1000),
                'response_at' => new Expression('NOW()')
            ]);
        } else {
            throw new \Exception(\Yii::t('api_web', "Response already recorded.", ['ru'=>'Ответ уже записан']), 999);
        }
    }

    /**
     * @param string $type
     */
    public static function setType($type)
    {
        self::update([
            'type' => $type
        ]);
    }

    /**
     * @param $user User
     * @throws \Exception
     */
    public static function setUser($user)
    {
        /**
         * @var $user User
         */
        if (!empty($user)) {
            if (!empty(self::get()['user_id'])) {
                throw new \Exception(\Yii::t('api_web', 'User already recorded.', ['ru'=>'Пользователь уже записан']), 999);
            }
            self::update([
                'user_id' => $user->id,
                'organization_id' => $user->organization->id ?? null
            ]);
        }
    }

    /**
     * @param $columns
     * @throws \yii\db\Exception
     */
    private static function insert($columns)
    {
        if (Yii::$app->params['web_api_log'] == true) {
            Yii::$app->db->createCommand()->insert(self::$tableName, $columns)->execute();
        }
    }

    /**
     * @param $columns
     * @throws \yii\db\Exception
     */
    private static function update($columns)
    {
        if (Yii::$app->params['web_api_log'] == true) {
            Yii::$app->db->createCommand()->update(self::$tableName, $columns, ['guide' => self::$guide])->execute();
        }
    }

    /**
     * @return array|bool
     */
    private static function get()
    {
        return (new Query())->select('*')->from(self::$tableName)->where(['guide' => self::$guide])->one();
    }
}