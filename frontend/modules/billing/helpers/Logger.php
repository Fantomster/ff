<?php

namespace frontend\modules\billing\helpers;

use Yii;
use yii\db\Expression;

class Logger
{
    private static $tableName = 'billing_logs';

    /**
     * @param null $data
     */
    public static function log($data = null)
    {
        $insert = [
            'date' => new Expression('NOW()'),
            'url' => Yii::$app->request->getUrl(),
            'headers' => static::getHeaders(),
            'method' => Yii::$app->request->getMethod(),
            'ip' => Yii::$app->request->getUserIP()
        ];

        try {
            if (empty($data)) {
                return;
            }
            if (is_array($data) or is_object($data)) {
                $data = print_r($data, true);
            }
            $insert['message'] = $data;
            Yii::$app->db->createCommand()->insert(static::$tableName, $insert)->execute();
        } catch (\Exception $e) {
            $insert['message'] = $e->getMessage();
            Yii::$app->db->createCommand()->insert(static::$tableName, $insert)->execute();
        }
    }

    /**
     * @return string
     */
    private static function getHeaders()
    {
        $headers = Yii::$app->request->getHeaders()->toArray();
        $return = '';
        foreach ($headers as $header => $value) {
            $value = implode('', $value);
            $return .= $header . ': ' . $value . PHP_EOL;
        }
        return $return;
    }
}