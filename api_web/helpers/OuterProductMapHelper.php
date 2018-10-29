<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 23/10/2018
 * Time: 11:35
 */

namespace api_web\helpers;

use common\helpers\DBNameHelper;
use common\models\IntegrationSettingValue;
use common\models\Order;
use common\models\OrderContent;
use yii\db\Query;

/**
 * Class OuterProductMapHelper
 *
 * @package api_web\helpers
 */
class OuterProductMapHelper
{
    /**
     * @param Order $order
     * @param int   $serviceId
     * @param int   $productId
     * @return array
     */
    public function getMapForOrder(Order $order, $serviceId, $productId = null)
    {
        $mainOrg = IntegrationSettingValue::getSettingsByServiceId($serviceId, $order->client_id, ['main_org']);
        $arProductIds = $productId ?? $this->mapOrderContentToIds($order->orderContent);
        return (new Query())->distinct()
            ->select([
                'a.product_id',
                'a.outer_product_id',
                'b.outer_store_id',
                'coalesce(b.vat , a.vat) vat',
                'coalesce(b.coefficient , a.coefficient) coefficient',
            ])
            ->from('outer_product_map a')
            ->leftJoin('outer_product_map b', 'b.organization_id = :real_org_id and a.product_id = b.product_id',
                ['real_org_id' => $order->client_id])
            ->where([
                'a.organization_id' => empty($mainOrg) ? $order->client_id : $mainOrg,
                'a.product_id'      => $arProductIds,
                'a.service_id'      => $serviceId,
                'a.vendor_id'       => $order->vendor_id,
            ])->all(\Yii::$app->db_api);
    }

    /**
     * @param array $orderContent
     * @return array
     */
    public function mapOrderContentToIds(array $orderContent)
    {
        return array_map(function ($el) {
            /**@var OrderContent $el */
            return $el->product_id;
        }, $orderContent);
    }
}