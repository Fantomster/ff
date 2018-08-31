<?php
/**
 * Created by PhpStorm.
 * User: xsupervisor
 * Date: 31.08.2018
 * Time: 14:42
 */

namespace frontend\modules\clientintegr\components;

interface CreateWaybillByOrderInterface
{

    public static function createWaybill($order_id) : bool;

    public static function exportWaybill($waybill_id) : bool;


}