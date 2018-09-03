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
}