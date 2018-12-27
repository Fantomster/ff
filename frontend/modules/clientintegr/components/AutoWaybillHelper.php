<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 28.08.2018
 * Time: 19:41
 */

namespace frontend\modules\clientintegr\components;

use api\common\models\iiko\iikoWaybill;
use api\common\models\one_s\OneSWaybill;
use api\common\models\RkDicconst;
use api\common\models\RkWaybill;
use common\models\User;
use api\common\models\iiko\iikoDicconst;

class AutoWaybillHelper extends \yii\base\Component
{

    const supportServices = [
        1 => RkWaybill::class,
        2 => iikoWaybill::class,
        8 => OneSWaybill::class,
    ];

    const licensesMap = [
        'rkws' => 1,
        'iiko' => 2,
        //   'odinsobs' => 8,
    ];

    public static function processWaybill($order_id)
    {
        $licenses = (User::findOne(\Yii::$app->user->id))->organization->getLicenseList();

        $res['rkws'] = true;
        $res['iiko'] = true;

        if (isset($licenses['rkws']) && ($licenses['rkws_ucs']) && array_key_exists('rkws', self::licensesMap)) {
            $className = self::supportServices[self::licensesMap['rkws']];
            $waybillModeRkws = RkDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
            $waybillExists = $className::find()->where(['order_id' => $order_id])->exists();

            if (!$waybillExists && ($waybillModeRkws !== '0')) {
                $res['rkws'] = $className::createWaybill($order_id);
            }
        }

        if (isset($licenses['iiko']) && array_key_exists('iiko', self::licensesMap)) {
            $className = self::supportServices[self::licensesMap['iiko']];
            $waybillModeIiko = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
            $waybillExists = $className::find()->where(['order_id' => $order_id])->exists();

            if (!$waybillExists && ($waybillModeIiko !== '0')) {
                $res['iiko'] = $className::createWaybill($order_id);
                if ($waybillModeIiko === '1') {
                    $res['iiko'] = $className::exportWaybill($order_id, true);
                }
            }
        }

        /*
        if (isset($licenses['odinsobsh']) && array_key_exists('odinsobsh', self::licensesMap)) {
            if (isset(self::licensesMap['odinsobsh'])) {
                $className = self::supportServices[self::licensesMap['odinsobsh']];
            }
        }
        */

        return ($res['rkws'] && $res['iiko']);
    }
}