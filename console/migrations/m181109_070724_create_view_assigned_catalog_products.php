<?php

use yii\db\Migration;

class m181109_070724_create_view_assigned_catalog_products extends Migration
{
    public function safeUp()
    {
        $this->execute('CREATE VIEW assigned_catalog_products AS
select a.id relation_supp_rest_id, a.rest_org_id, a.supp_org_id, a.invite relation_supp_rest_invite,
       a.status relation_supp_rest_status, a.deleted relation_supp_rest_deleted,
       b.id catalog_id, b.type catalog_type, b.name catalog_name, b.status catalog_status,
       coalesce(c.id, e.id) product_id,
       coalesce(c.article, e.article) article,
       coalesce(c.product, e.product) product,
       coalesce(c.status, e.status) status,
       coalesce(c.market_place, e.market_place) market_place,
       coalesce(c.deleted, e.deleted) deleted,
       coalesce(c.created_at, d.created_at) created_at,
       coalesce(c.updated_at, d.updated_at) updated_at,
       coalesce(c.price, d.price) price,
    coalesce(c.units, e.units) units,
    coalesce(c.category_id, e.category_id) category_id,
    coalesce(c.note, e.note) note,
    coalesce(c.ed, e.ed) ed,
    coalesce(c.image, e.image) image,
    coalesce(c.brand, e.brand) brand,
    coalesce(c.region, e.region) region,
    coalesce(c.weight, e.weight) weight,
    coalesce(c.es_status, e.es_status) es_status,
    coalesce(c.mp_show_price, e.mp_show_price) mp_show_price,
    coalesce(c.rating, e.rating) rating,
    coalesce(c.barcode, e.barcode) barcode,
    coalesce(c.edi_supplier_article, e.edi_supplier_article) edi_supplier_article,    coalesce(c.ssid, e.ssid) ssid,
    d.discount_percent,
    d.discount,
    d.discount_fixed
from relation_supp_rest a
  join catalog b on a.cat_id = b.id
  left join catalog_base_goods c on c.cat_id = b.id
  left join catalog_goods d on d.cat_id = b.id
  left join catalog_base_goods e on e.id = d.base_goods_id;');
    }

    public function safeDown()
    {
        $this->execute('DROP VIEW assigned_catalog_products;');
    }
}
