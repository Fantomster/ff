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
use api\common\models\RkWaybill;
use common\models\User;

class AutoWaybillHelper extends \yii\base\Component
{

    const supportServices = [
        1 => RkWaybill::class,
        2 => iikoWaybill::class,
        8 => OneSWaybill::class,
    ];

    const licensesMap = [
    //    'rkws' => 1,
        'iiko' => 2,
     //   'odinsobs' => 8,
    ];

    public static function processWaybill($order_id)
    {

        var_dump(2222);
        $licenses = (User::findOne(\Yii::$app->user->id))->organization->getLicenseList();

            if(isset($licenses['rkws']) && ($licenses['rkws_ucs']) && array_key_exists('rkws', self::licensesMap)) {
                $className = self::supportServices[self::licensesMap['rkws']];
            }

            if(isset($licenses['iiko']) && array_key_exists('iiko', self::licensesMap)) {
                $className = self::supportServices[self::licensesMap['iiko']];
            }
            if(isset($licenses['odinsobsh']) &&  array_key_exists('odinsobsh', self::licensesMap)) {
                $className = self::supportServices[self::licensesMap['odinsobsh']];
            }

            return $className::createWaybill($order_id);

    }
}