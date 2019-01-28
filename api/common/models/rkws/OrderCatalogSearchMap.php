<?php

namespace api\common\models\rkws;

use api\common\models\AllMaps;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\iikoStore;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSStore;
use api\common\models\RkProduct;
use api\common\models\RkStoretree;
use api_web\modules\integration\modules\one_s\models\one_sProduct;
use common\helpers\DBNameHelper;
use common\models\AllService;
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
     * @param array $params
     * @return \yii\data\ActiveDataProvider|SqlDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        $dbName = DBNameHelper::getApiName();
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
            $where = ' (acp.product  LIKE :searchString OR acp.article LIKE :searchString)';
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

        $query1 = (new \yii\db\Query())
            ->select("  a.id AS relation_supp_rest_id,
                                        a.rest_org_id AS rest_org_id,
                                        a.supp_org_id AS supp_org_id,
                                        a.invite AS relation_supp_rest_invite,
                                        a.status AS relation_supp_rest_status,
                                        a.deleted AS relation_supp_rest_deleted,
                                        b.id AS catalog_id,
                                        b.type AS catalog_type,
                                        b.name AS catalog_name,
                                        b.status AS catalog_status,
                                        c.id AS product_id,
                                        c.article AS article,
                                        c.product AS product,
                                        c.status AS status,
                                        c.market_place AS market_place,
                                        c.deleted AS deleted,
                                        c.created_at AS created_at,
                                        c.updated_at AS updated_at,
                                        c.price AS price,
                                        c.units AS units,
                                        c.category_id AS category_id,
                                        c.note AS note,
                                        c.ed AS ed,
                                        c.image AS image,
                                        c.brand AS brand,
                                        c.region AS region,
                                        c.weight AS weight,
                                        c.es_status AS es_status,
                                        c.mp_show_price AS mp_show_price,
                                        c.rating AS rating,
                                        c.barcode AS barcode,
                                        c.edi_supplier_article AS edi_supplier_article,
                                        c.ssid AS ssid")
            ->from('(('.RelationSuppRest::tableName().' a
                                    join '.Catalog::tableName().' b on
                                        ((a.cat_id = b.id)))
                                    join '.CatalogBaseGoods::tableName().' c on
                                        ((c.cat_id = b.id)))')
            ->where("(b.type = 1) AND a.rest_org_id = $client_id AND a.supp_org_id in ($vendorInList)
                                            AND b.status = 1
                                            AND a.deleted = 0");

        $query2 = (new \yii\db\Query())
            ->select("a.id AS relation_supp_rest_id,
                                        a.rest_org_id AS rest_org_id,
                                        a.supp_org_id AS supp_org_id,
                                        a.invite AS relation_supp_rest_invite,
                                        a.status AS relation_supp_rest_status,
                                        a.deleted AS relation_supp_rest_deleted,
                                        b.id AS catalog_id,
                                        b.type AS catalog_type,
                                        b.name AS catalog_name,
                                        b.status AS catalog_status,
                                        d.id AS product_id,
                                        d.article AS article,
                                        d.product AS product,
                                        d.status AS status,
                                        d.market_place AS market_place,
                                        d.deleted AS deleted,
                                        d.created_at AS created_at,
                                        d.updated_at AS updated_at,
                                        c.price AS price,
                                        d.units AS units,
                                        d.category_id AS category_id,
                                        d.note AS note,
                                        d.ed AS ed,
                                        d.image AS image,
                                        d.brand AS brand,
                                        d.region AS region,
                                        d.weight AS weight,
                                        d.es_status AS es_status,
                                        d.mp_show_price AS mp_show_price,
                                        d.rating AS rating,
                                        d.barcode AS barcode,
                                        d.edi_supplier_article AS edi_supplier_article,
                                        d.ssid AS ssid")
            ->from("  (((".RelationSuppRest::tableName()." a
                                    join ".Catalog::tableName()." b on
                                        ((a.cat_id = b.id)))
                                    join ".CatalogGoods::tableName()." c on
                                        ((c.cat_id = b.id)))
                                    join ".CatalogBaseGoods::tableName()." d on
                                        ((d.id = c.base_goods_id)))")
            ->where(" (b.type = 1) AND a.rest_org_id = $client_id AND a.supp_org_id in ($vendorInList)
                                            AND b.status = 1
                                            AND a.deleted = 0");

        $assigned_catalog_products = (new \yii\db\Query())->from(['tb1' => $query1->union($query2)]);

        if ($this->service_id == 0) {
            $sql = (new \yii\db\Query())
                ->select("acp.catalog_id as cat_id,acp.product_id as id,acp.product,acp.article,acp.ed,amap.id as amap_id,amap.vat as vat,amap.koef as koef,amap.service_id as service_id,aser.denom as service_denom" . $fields[$this->service_id])
                ->from(['acp' => $assigned_catalog_products])
                ->leftJoin("$dbName.".AllMaps::tableName(). " amap", "acp.product_id = amap.product_id AND amap.org_id = $client_id AND amap.service_id = :service_id", [':service_id' => $this->service_id])
                ->leftJoin("$dbName.".AllService::tableName()." aser", "amap.service_id = aser.id ")
                ->where("amap.service_id = 0");
            if (!empty($where)) {
                $sql->andWhere($where);
            }
        } else {
            $sql = (new \yii\db\Query())
                ->select("acp.catalog_id as cat_id,acp.product_id as id,acp.product,acp.article,acp.ed,amap.id as amap_id,amap.vat as vat,amap.koef as koef,amap.service_id as service_id,aser.denom as service_denom" . $fields[$this->service_id])
                ->from(['acp' => $assigned_catalog_products])
                ->leftJoin("$dbName.".AllMaps::tableName(). " amap", "acp.product_id = amap.product_id AND amap.org_id = $client_id AND amap.service_id = :service_id", [':service_id' => $this->service_id])
                ->leftJoin("$dbName.".AllService::tableName()." aser", "amap.service_id = aser.id ");
            if (!empty($where)) {
                $sql->Where($where);
            }
        }

        $this->addQueryJoins($sql, $this->service_id );
        $sql = $sql->createCommand()->getRawSql();
        if ($vendorInList) {
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
                        'id'
                    ],
                    'defaultOrder' => [
                        'product' => SORT_ASC,
                        'id'      => SORT_ASC,
                    ]
                ],
            ]);
        } else {
            $sql = (new \yii\db\Query())
                ->select("id")
                ->from(CatalogBaseGoods::tableName())
                ->where("id = 0"); // Запрос, заведомо возвращающий пустой результат во избежание ошибки у ресторана,

            // у которого нет ни одной записи в relation_supp_rest
            $dataProvider = new SqlDataProvider([
                'sql'        => $sql,
                'pagination' => [
                    'page'     => isset($params['page']) ? ($params['page'] - 1) : 0,
                    'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : null,
                ],
            ]);
        }
        return $dataProvider;
    }

    private function addQueryJoins(&$query, $service_id)
    {
        $dbName = DBNameHelper::getApiName();
        switch ($service_id) {

            case Registry::RK_SERVICE_ID :
                $query->leftJoin("$dbName.".RkProduct::tableName()." fprod", "amap.serviceproduct_id = fprod.id");
                $query->leftJoin("$dbName.".RkStoretree::tableName()." fstore", "amap.store_rid = fstore.id AND amap.org_id = fstore.acc  AND fstore.type = 2");
                break;
            case Registry::IIKO_SERVICE_ID :
                $query->leftJoin("$dbName.".iikoProduct::tableName()." fprod ", "amap.serviceproduct_id = fprod.id");
                $query->leftJoin("$dbName.".iikoStore::tableName()." fstore", "amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1");
                break;
            case Registry::ONE_S_CLIENT_SERVICE_ID :
                $query->leftJoin("$dbName.".OneSGood::tableName()." fprod", "amap.serviceproduct_id = fprod.id");
                $query->leftJoin("$dbName.".OneSStore::tableName()." fstore", "amap.store_rid = fstore.id AND amap.org_id = fstore.org_id");
                break;
            case Registry::TILLYPAD_SERVICE_ID :
                $query->leftJoin("$dbName".RkProduct::tableName()." fprod", "amap.serviceproduct_id = fprod.id");
                $query->leftJoin("$dbName".RkStoretree::tableName()." fstore", "amap.store_rid = fstore.id AND amap.org_id = fstore.org_id  AND fstore.is_active = 1");
                break;
        }
    }
}
