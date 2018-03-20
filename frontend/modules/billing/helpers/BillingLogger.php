<?php

namespace frontend\modules\billing\helpers;

use Yii;
use yii\db\Expression;

class BillingLogger
{
    const LOGGER_STATUS_SUCCESS = 'success';
    const LOGGER_STATUS_ERROR = 'error';
    const LOGGER_STATUS_ERROR_API = 'api_error';

    private static $tableName = 'billing_logs';

    /**
     * @param null $data
     * @param null $action
     * @param string $status
     * @throws \Exception
     */
    public static function log($data = null, $action = null, $status = self::LOGGER_STATUS_SUCCESS)
    {
        $insert = [
            'date' => new Expression('NOW()'),
            'url' => Yii::$app->request->getUrl(),
            'headers' => static::getHeaders(),
            'method' => Yii::$app->request->getMethod(),
            'ip' => Yii::$app->request->getUserIP(),
            'status' => $status,
            'action' => $action,
        ];

        if (is_array($data) or is_object($data)) {
            $data = \GuzzleHttp\json_encode($data);
        }

        $insert['message'] = $data;
        Yii::$app->db->createCommand()->insert(static::$tableName, $insert)->execute();
    }

    /**
     * @return string
     */
    private static function getHeaders()
    {
        $headers = Yii::$app->request->getHeaders()->toArray();
        $return = [];
        foreach ($headers as $header => $value) {
            $value = implode('', $value);
            $return[] = $header . ': ' . $value;
        }
        return \GuzzleHttp\json_encode($return);
    }
}