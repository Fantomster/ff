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

    /**
     * @return Query
     * */
    public function getOrdersQueryVetis()
    {
        $tableName = $this->getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $query = (new Query())
            ->select(
                [
                    'COALESCE(o.id, \'order_not_installed\' ) as group_name',
                    'COUNT(m.id) as count',
                    'o.created_at',
                    'o.total_price',
                    'GROUP_CONCAT(`wc`.`merc_uuid` SEPARATOR \',\') AS `uuids`'
                ]
            )
            ->from('`' . $tableName . '`.merc_vsd m')
            ->leftJoin('`' . $tableName . '`.waybill_content wc', 'wc.merc_uuid = m.uuid COLLATE utf8_unicode_ci')
            ->leftJoin('`' . $tableName . '`.waybill w', 'w.id = wc.waybill_id')
            ->leftJoin('order_content oc', 'oc.id = wc.order_content_id')
            ->leftJoin('order o', 'o.id = oc.order_id')
            ->where('w.service_id = 4')
            ->groupBy('group_name')
            ->orderBy(['group_name' => SORT_DESC]);

        return $query;
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