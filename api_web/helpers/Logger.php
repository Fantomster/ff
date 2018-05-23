<?php

namespace api_web\helpers;

use Yii;
use yii\db\Expression;
use common\models\User;

class Logger
{
    private static $tableName = 'web_api_log';

    private static $guide;
    private static $instance;

    function __clone(){}
    function __wakeup(){}

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
     */
    public static function request($request)
    {
        self::update([
            'request' => \json_encode($request, JSON_UNESCAPED_UNICODE),
            'request_at' => new Expression('NOW()')
        ]);
    }

    /**
     * @param $response
     */
    public static function response($response)
    {
        self::update([
            'response' => \json_encode($response, JSON_UNESCAPED_UNICODE),
            'response_at' => new Expression('NOW()')
        ]);
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
     * @param User $user
     */
    public static function setUser($user)
    {
        /**
         * @var $user User
         */
        if (!empty($user)) {
            self::update([
                'user_id' => $user->id,
                'organization_id' => $user->organization->id
            ]);
        }
    }

    /**
     * @param $columns
     */
    private static function insert($columns)
    {
        if (Yii::$app->params['web_api_log'] == true) {
            Yii::$app->db->createCommand()->insert(self::$tableName, $columns)->execute();
        }
    }

    /**
     * @param $columns
     */
    private static function update($columns)
    {
        if (Yii::$app->params['web_api_log'] == true) {
            Yii::$app->db->createCommand()->update(self::$tableName, $columns, ['guide' => self::$guide])->execute();
        }
    }
}