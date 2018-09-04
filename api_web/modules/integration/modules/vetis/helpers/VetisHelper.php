<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 8/30/2018
 * Time: 4:25 PM
 */

namespace api_web\modules\integration\modules\vetis\helpers;


use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class VetisHelper
{
    /**
     * @return ActiveQuery
     * */
    public function getQueryByUuid()
    {
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        return MercVsd::find()->where(['recipient_guid' => $enterpriseGuid]);
    }

    public function isSetDef($param, $default = null)
    {
        if (isset($param) && !empty($param)) {
            return $param;
        }
        return $default;
    }

    public function set(&$var, $arParams, $arLabels)
    {
        $arGoodParams = [];
        foreach ($arLabels as $label) {
            if (isset($arParams[$label]) && !empty($arParams[$label])) {
                if ($label == 'date') {
                    $this->set($var, $arParams[$label], ['from', 'to']);
                } else {
                    $var->{$label} = $arParams[$label];
                    $arGoodParams[$label] = $arParams[$label];
                }
            }
        }

        return $arGoodParams;
    }

    public function getOrdersVetis()
    {
        $tableName = $this->getDsnAttribute('dbname', \Yii::$app->db->dsn);
        $sq = 'SELECT
                  COALESCE(o.id, "order_not_installed") as group_name,
                  COUNT(m.id),
                  o.created_at,
                  o.total_price
                FROM merc_vsd m
                LEFT JOIN waybill_content wc ON wc.merc_uuid = m.uuid COLLATE utf8_unicode_ci
                LEFT JOIN waybill w ON w.id = wc.waybill_id
                LEFT JOIN ' . $tableName .'.`order_content` oc ON oc.id = wc.order_content_id
                LEFT JOIN ' . $tableName .'.`order` o ON o.id = oc.order_id
                WHERE
                  w.service_id = 4
                GROUP BY group_name
                ORDER BY group_name DESC';

        
        $posts = \Yii::$app->db_api->createCommand($sq)->queryAll();
        return $posts;
    }

    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }
}