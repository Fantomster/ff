<?php

namespace api_web\modules\integration\modules\iiko\helpers;

use api_web\modules\integration\modules\iiko\models\iikoService;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use common\models\User;
use yii\helpers\ArrayHelper;

class iikoLogger
{

    private static $tableName = '{{%iiko_log}}';
    private static $guide;
    private static $row;

    private static $instance;

    function __clone()
    {
    }

    function __wakeup()
    {
    }

    function __construct()
    {
        self::$guide = md5(uniqid(microtime(), 1));
        self::insert([
            'guide' => self::$guide,
            'ip' => Yii::$app->request->getUserIP()
        ]);
        $this->setUser(\Yii::$app->user->id);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $denom
     * @return array|bool
     * @throws \Exception
     */
    public static function setOperation($denom)
    {
        $operation = (new Query())
            ->select('code')
            ->from('all_service_operation')
            ->where(['service_id' => iikoService::getServiceId(), 'denom' => $denom])
            ->one(Yii::$app->db_api);

        if (!empty($operation)) {
            self::update(['operation_code' => $operation['code']]);
        } else {
            throw new \Exception('Not found operation service_id:' . iikoService::getServiceId() . ' demon:' . $denom, 999);
        }
    }

    /**
     * @param $request
     * @throws \Exception
     */
    public static function request($request)
    {
        self::update([
            'request' => \json_encode($request, JSON_UNESCAPED_UNICODE),
            'request_at' => Yii::$app->formatter->asDatetime(time(),'yyyy-MM-dd HH:i:ss')
        ]);
    }

    /**
     * @param $response
     * @throws \Exception
     */
    public static function response($response)
    {
        self::update([
            'response' => \json_encode($response, JSON_UNESCAPED_UNICODE),
            'response_at' => Yii::$app->formatter->asDatetime(time(),'yyyy-MM-dd HH:i:ss')
        ]);

        self::saveToFile();
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
     * @param $user_id
     * @throws \Exception
     */
    private static function setUser($user_id)
    {
        $user = User::findOne($user_id);
        if (!empty($user)) {
            if (!empty(self::get()['user_id'])) {
                throw new \Exception('User already recorded.', 999);
            }
            self::update([
                'user_id' => $user->id,
                'organization_id' => $user->organization->id ?? null
            ]);
        }
    }

    /**
     * @param $columns
     */
    private static function insert($columns)
    {
        self::$row[$columns['guide']] = $columns;
    }

    /**
     * @param $columns
     */
    private static function update($columns)
    {
        self::$row[self::$guide] = ArrayHelper::merge(self::$row[self::$guide], $columns);
    }

    /**
     * @return array|bool
     */
    private static function get()
    {
        return self::$row[self::$guide];
    }

    /**
     * логируем запросы к базе, в файл, чтобы не попасть в транзакцию.
     */
    private static function saveToFile()
    {
        //$row = \Yii::$app->db_api->createCommand()->insert(self::$tableName, self::$row[self::$guide])->getRawSql() . ';' . PHP_EOL;
        //file_put_contents(Yii::getAlias('@runtime') . '/iiko_log.log', $row, FILE_APPEND);
        self::$instance = null;
    }

    /**
     * Сохранение действий в базу
     */
    public static function save()
    {
        /*$file = Yii::getAlias('@runtime') . '/iiko_log.log';
        if (file_exists($file)) {
            $sql = file($file);
            if (!empty($sql)) {
                $sql_raw = '';
                foreach ($sql as $insert) {
                    $sql_raw .= $insert;
                }
                \Yii::$app->db_api->createCommand($sql_raw)->execute();
            }
            unlink($file);
        }*/
    }
}