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

class VetisHelper
{
    /**
     * @return ActiveQuery
     * */
    public function getQueryByUuid(){
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        return MercVsd::find()->where(['recipient_guid' => $enterpriseGuid]);
    }
}