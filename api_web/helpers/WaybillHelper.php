<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/29/2018
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use common\models\Waybill;
use common\models\WaybillContent;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    
    /**
     * Create waybill and waybill_content and binding VSD
     * @param string $uuid VSD uuid
     * @return boolean
     * */
    public function createWaybill($uuid)
    {
        $transaction = \Yii::$app->db_api->beginTransaction();
        $orgId = (\Yii::$app->user->identity)->organization_id;
        $modelWaybill = new Waybill();
        $modelWaybill->acquirer_id = $orgId;
        $modelWaybill->service_id = self::MERC_SERVICE_ID;
        
        $modelWaybillContent = new WaybillContent();
        $modelWaybillContent->merc_uuid = $uuid;
        try {
            $modelWaybill->save();
            $modelWaybillContent->waybill_id = $modelWaybill->id;
            $modelWaybillContent->save();
            $transaction->commit();
        } catch (\Throwable $t) {
            $transaction->rollBack();
            \Yii::error($t->getMessage(), __METHOD__);
            return false;
        }
        
        return true;
    }
}