<?php

namespace api\common\models\rkws;

use api\common\models\iiko\iikoService;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\RelationSuppRest;
use yii\data\SqlDataProvider;
use common\models\Catalog;
use api_web\components\Registry;
use Yii;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderCatalogSearchMap extends \common\models\search\OrderCatalogSearch
{
    public $product_rid;
    public $vat;
    public $store;
    public $koef;
    public $pdenom;
    public $service_id;
    public $vendors;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['product_rid', 'vat', 'store', 'koef', 'pdenom', 'service_id'], 'safe'],
            //   [['page','count'], 'integer']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'product_rid'   => 'Продукт в системе Заказчика',
            'store'         => 'Склад',
            'koef'          => 'Коэффициент',
            'vat'           => 'Ставка НДС',
            'pdenom'        => 'Название продукта сопоставления',
            'service_id'    => 'Сервис',
            'service_denom' => 'Сервис'
        ];
    }

    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        $db_api = \Yii::$app->db_api;
        $dbName = $this->getDsnAttribute('dbname', $db_api->dsn);
        if (empty($this->service_id)) {
            $this->service_id = 0;
        }
        $fields = [
            0                                 => '',
            Registry::RK_SERVICE_ID           => ',fprod.denom as pdenom, fstore.name as store, fprod.unitname as unitname', // R-keeper
            Registry::IIKO_SERVICE_ID         => ',fprod.denom as pdenom, fstore.denom as store, fprod.unit as unitname', // iiko
            Registry::ONE_S_CLIENT_SERVICE_ID => ',fprod.name as pdenom, fstore.name as store, fprod.measure as unitname', // 1C
            Registry::TILLYPAD_SERVICE_ID     => ',fprod.denom as pdenom, fstore.denom as store, fprod.unit as unitname', // tillypad
        ];

        $joins = [
            0                       => '',
            Registry::RK_SERVICE_ID => " LEFT JOIN `$dbName`.`rk_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`rk_storetree` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.acc  AND fstore.type = 2 ",

            Registry::IIKO_SERVICE_ID => " LEFT JOIN `$dbName`.`iiko_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`iiko_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1 ",

            Registry::ONE_S_CLIENT_SERVICE_ID => " LEFT JOIN `$dbName`.`one_s_good` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`one_s_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id ",

            Registry::TILLYPAD_SERVICE_ID => " LEFT JOIN `$dbName`.`iiko_product` fprod ON amap.serviceproduct_id = fprod.id
                   LEFT JOIN `$dbName`.`iiko_store` fstore ON amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1 ",

        ];

        $where = '';
        $params_sql = [];

        if (!empty($this->selectedVendor)) {
            if (is_array($this->selectedVendor)) {
                foreach ($this->selectedVendor as $key => $supp_org_id) {
                    $this->selectedVendor[$key] = (int)$supp_org_id;
                }
                $this->selectedVendor = implode(', ', $this->selectedVendor);
            } else {
                $this->selectedVendor = (int)$this->selectedVendor;
            }
        }

        if (!empty($this->searchString)) {
            $where .= ' (acp.product  LIKE :searchString OR acp.article LIKE :searchString)';
            $params_sql[':searchString'] = "%" . $this->searchString . "%";
        }

        $client_id = $this->client->id;
        $vendorInList = $this->selectedVendor;

        if (isset($this->vendors) && empty($this->selectedVendor)) {
            $arrayVendorsId = array_keys($this->vendors);
            unset($arrayVendorsId[0]);
            $arrayVendorsId = implode(",", $arrayVendorsId);
            $vendorInList = $arrayVendorsId;
        }
        $assigned_catalog_products = "select
                                        `a`.`id` AS `relation_supp_rest_id`,
                                        `a`.`rest_org_id` AS `rest_org_id`,
                                        `a`.`supp_org_id` AS `supp_org_id`,
                                        `a`.`invite` AS `relation_supp_rest_invite`,
                                        `a`.`status` AS `relation_supp_rest_status`,
                                        `a`.`deleted` AS `relation_supp_rest_deleted`,
                                        `b`.`id` AS `catalog_id`,
                                        `b`.`type` AS `catalog_type`,
                                        `b`.`name` AS `catalog_name`,
                                        `b`.`status` AS `catalog_status`,
                                        `c`.`id` AS `product_id`,
                                        `c`.`article` AS `article`,
                                        `c`.`product` AS `product`,
                                        `c`.`status` AS `status`,
                                        `c`.`market_place` AS `market_place`,
                                        `c`.`deleted` AS `deleted`,
                                        `c`.`created_at` AS `created_at`,
                                        `c`.`updated_at` AS `updated_at`,
                                        `c`.`price` AS `price`,
                                        `c`.`units` AS `units`,
                                        `c`.`category_id` AS `category_id`,
                                        `c`.`note` AS `note`,
                                        `c`.`ed` AS `ed`,
                                        `c`.`image` AS `image`,
                                        `c`.`brand` AS `brand`,
                                        `c`.`region` AS `region`,
                                        `c`.`weight` AS `weight`,
                                        `c`.`es_status` AS `es_status`,
                                        `c`.`mp_show_price` AS `mp_show_price`,
                                        `c`.`rating` AS `rating`,
                                        `c`.`barcode` AS `barcode`,
                                        `c`.`edi_supplier_article` AS `edi_supplier_article`,
                                        `c`.`ssid` AS `ssid`,
                                        NULL AS `discount_percent`,
                                        NULL AS `discount`,
                                        NULL AS `discount_fixed`
                                    from
                                        ((`relation_supp_rest` `a`
                                    join `catalog` `b` on
                                        ((`a`.`cat_id` = `b`.`id`)))
                                    join `catalog_base_goods` `c` on
                                        ((`c`.`cat_id` = `b`.`id`)))
                                    where
                                        (`b`.`type` = 1) AND `a`.rest_org_id = $client_id AND `a`.supp_org_id in ($vendorInList)
                                            AND `b`.`status` = 1
                                            AND `a`.deleted = 0
                                    union all select
                                        `a`.`id` AS `relation_supp_rest_id`,
                                        `a`.`rest_org_id` AS `rest_org_id`,
                                        `a`.`supp_org_id` AS `supp_org_id`,
                                        `a`.`invite` AS `relation_supp_rest_invite`,
                                        `a`.`status` AS `relation_supp_rest_status`,
                                        `a`.`deleted` AS `relation_supp_rest_deleted`,
                                        `b`.`id` AS `catalog_id`,
                                        `b`.`type` AS `catalog_type`,
                                        `b`.`name` AS `catalog_name`,
                                        `b`.`status` AS `catalog_status`,
                                        `c`.`id` AS `product_id`,
                                        `d`.`article` AS `article`,
                                        `d`.`product` AS `product`,
                                        `d`.`status` AS `status`,
                                        `d`.`market_place` AS `market_place`,
                                        `d`.`deleted` AS `deleted`,
                                        `d`.`created_at` AS `created_at`,
                                        `d`.`updated_at` AS `updated_at`,
                                        `c`.`price` AS `price`,
                                        `d`.`units` AS `units`,
                                        `d`.`category_id` AS `category_id`,
                                        `d`.`note` AS `note`,
                                        `d`.`ed` AS `ed`,
                                        `d`.`image` AS `image`,
                                        `d`.`brand` AS `brand`,
                                        `d`.`region` AS `region`,
                                        `d`.`weight` AS `weight`,
                                        `d`.`es_status` AS `es_status`,
                                        `d`.`mp_show_price` AS `mp_show_price`,
                                        `d`.`rating` AS `rating`,
                                        `d`.`barcode` AS `barcode`,
                                        `d`.`edi_supplier_article` AS `edi_supplier_article`,
                                        `d`.`ssid` AS `ssid`,
                                        `c`.`discount_percent` AS `discount_percent`,
                                        `c`.`discount` AS `discount`,
                                        `c`.`discount_fixed` AS `discount_fixed`
                                    from
                                        (((`relation_supp_rest` `a`
                                    join `catalog` `b` on
                                        ((`a`.`cat_id` = `b`.`id`)))
                                    join `catalog_goods` `c` on
                                        ((`c`.`cat_id` = `b`.`id`)))
                                    join `catalog_base_goods` `d` on
                                        ((`d`.`id` = `c`.`base_goods_id`)))
                                    where
                                        (`b`.`type` = 2) AND `a`.rest_org_id = $client_id AND `a`.supp_org_id in ($vendorInList)
                                            AND `b`.`status` = 1
                                            AND `a`.deleted = 0
	";
        if ($this->service_id==0) {
            $sql = "SELECT acp.catalog_id as cat_id,acp.product_id as id,acp.product,acp.article,acp.ed,amap.id as amap_id,amap.vat as vat,amap.koef as koef,amap.service_id as service_id,aser.denom as service_denom" . $fields[$this->service_id] .
                " FROM ($assigned_catalog_products) `acp`
            LEFT JOIN `$dbName`.`all_map` `amap` ON acp.product_id = amap.product_id AND amap.org_id = " . $client_id . " AND amap.service_id = " . $this->service_id . " 
            LEFT JOIN `$dbName`.`all_service` `aser` ON amap.service_id = aser.id " . $joins[$this->service_id] . "
            WHERE amap.service_id = 0" . empty($where) ? "" : " AND " . $where;
        } else {
            $sql = "SELECT acp.catalog_id as cat_id,acp.product_id as id,acp.product,acp.article,acp.ed,amap.id as amap_id,amap.vat as vat,amap.koef as koef,amap.service_id as service_id,aser.denom as service_denom" . $fields[$this->service_id] .
                " FROM ($assigned_catalog_products) `acp`
            LEFT JOIN `$dbName`.`all_map` `amap` ON acp.product_id = amap.product_id AND amap.org_id = " . $client_id . " AND amap.service_id = " . $this->service_id . " 
            LEFT JOIN `$dbName`.`all_service` `aser` ON amap.service_id = aser.id " . $joins[$this->service_id] .
            empty($where) ? "" : " WHERE " . $where;
        }

        $dataProvider = new SqlDataProvider([
            'sql'    => $sql,
            'params' => $params_sql,

            'pagination' => [
                'page'     => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                'params'   => [
                    'sort' => isset($params['sort']) ? $params['sort'] : 'product',
                ]
            ],
            'key'        => 'id',
            'sort'       => [
                'attributes'   => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC,
                ]
            ],
        ]);
        return $dataProvider;
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
