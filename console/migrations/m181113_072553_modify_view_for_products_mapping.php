<?php

use yii\db\Migration;

/**
 * Class m181113_072553_add_view_for_products_mapping
 */
class m181113_072553_modify_view_for_products_mapping extends Migration
{

    public function safeUp()
    {
        $this->execute('CREATE
OR REPLACE
VIEW `assigned_catalog_products` AS select
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
    (`a`.`cat_id` = `b`.`id`))
join `catalog_base_goods` `c` on
    (`c`.`cat_id` = `b`.`id`))
where
    `b`.`type` = 1
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
    (`a`.`cat_id` = `b`.`id`))
join `catalog_goods` `c` on
    (`c`.`cat_id` = `b`.`id`))
join `catalog_base_goods` `d` on
    (`d`.`id` = `c`.`base_goods_id`))
where
    `b`.`type` = 2');
    }

    public function safeDown()
    {
        $this->execute('DROP VIEW assigned_catalog_products;');
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m181113_072553_add_view_for_products_mapping cannot be reverted.\n";

      return false;
      }
     */
}
