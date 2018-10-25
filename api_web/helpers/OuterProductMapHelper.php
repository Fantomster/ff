<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 23/10/2018
 * Time: 11:35
 */

namespace api_web\helpers;

use common\helpers\DBNameHelper;
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
     * @param int   $mainOrg
     * @return array
     */
    public function getMapForOrder(Order $order, $serviceId, $mainOrg)
    {
        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);
        $arProductIds = $this->mapOrderContentToIds($order->orderContent);

        return (new Query())->distinct()->select([
            'a.product_id',
            'a.outer_product_id master_serviceproduct_id',
            'b.outer_product_id',
            'b.outer_store_id',
            'coalesce(b.vat , a.vat) vat',
            'coalesce(b.coefficient , a.coefficient) coefficient',
        ])->from('outer_product_map a')
            ->leftJoin('outer_product_map b', 'b.organization_id = :real_org_id and a.product_id = b.product_id',
                ['real_org_id' => $order->client_id])
            ->where([
                'a.organization_id' => $mainOrg ?? $order->client_id,
                'a.product_id'      => $arProductIds,
                'a.service_id'      => $serviceId,
                'a.vendor_id'       => $order->vendor_id,
            ])->all(\Yii::$app->db_api);

        return (new Query())->select([
            'm.outer_store_id as outer_store_id',
            'GROUP_CONCAT(oc.product_id) as prd_ids'])
            ->from('order_content oc')
            ->leftJoin('`' . $dbName . '`.outer_product_map m', 'oc.product_id = m.product_id AND m.service_id = :service_id AND m.organization_id = :org_id AND m.vendor_id = :vendor_id', [':service_id' => $serviceId, ':org_id' => $order->client_id, ':vendor_id' => $order->vendor_id])
            ->where(['oc.order_id' => $order->id])
            ->andWhere(['not', ['m.outer_store_id' => null]])
            ->groupBy('m.outer_store_id')->all();
    }

    /**
     * @param array $orderContent
     * @return array
     */
    public function mapOrderContentToIds(array $orderContent){
        return array_map(function ($el) {
            /**@var OrderContent $el */
            return $el->product_id;
        }, $orderContent);
    }
}