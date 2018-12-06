<?php

namespace api_web\modules\integration\modules\one_s\helpers;

use api_web\modules\integration\modules\one_s\models\one_sService;
use Yii;
use yii\db\Query;
use common\models\User;
use yii\helpers\ArrayHelper;

class one_sLogger
{

    public static $tableName = '{{%one_s_log}}';
    private static $guide;
    private static $row;

    function __construct()
    {
        self::$guide = md5(uniqid(microtime(), 1));
        self::insert([
            'guide' => self::$guide,
            'ip' => Yii::$app->request->getUserIP()
        ]);
        $this->setUser(\Yii::$app->user->id);
    }

    /**
     * @param $denom
     * @return void
     * @throws \Exception
     */
    public function setOperation($denom)
    {
        $operation = (new Query())
            ->select('code')
            ->from('all_service_operation')
            ->where(['service_id' => OneSService::getServiceId(), 'denom' => $denom])
            ->one(Yii::$app->db_api);

        if (!empty($operation)) {
            self::update(['operation_code' => $operation['code']]);
        } else {
            throw new \Exception('Not found operation service_id:' . one_sService::getServiceId() . ' demon:' . $denom, 999);
        }
    }

    /**
     * @param $request
     * @throws \Exception
     */
    public function request($request)
    {
        self::update([
            'request' => \json_encode($request, JSON_UNESCAPED_UNICODE),
            'request_at' => Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:i:ss')
        ]);
    }

    /**
     * @param $response
     * @throws \Exception
     */
    public function response($response)
    {
        self::update([
            'response' => \json_encode($response, JSON_UNESCAPED_UNICODE),
            'response_at' => Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:i:ss')
        ]);

        \Yii::$app->get('rabbit')
            ->setQueue(self::getNameQueue())
            ->setExchange('log')
            ->addRabbitQueue(\json_encode(self::$row[self::$guide]));
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        self::update([
            'type' => $type
        ]);
    }

    /**
     * @param $user_id
     * @throws \Exception
     */
    private function setUser($user_id)
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
     * @return string
     */
    public static function getNameQueue()
    {
        return 'log_service_' . one_sService::getServiceId();
    }
}