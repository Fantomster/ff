<?php

use yii\db\Migration;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;

class m181116_154600_copy_products_base_catalogs_to_table_catalog_goods extends Migration
{
    public function safeUp()
    {
        $sql = 'SELECT DISTINCT `cat_id` FROM `relation_supp_rest` `rsr` LEFT JOIN `catalog` `c` ON `rsr`.`cat_id` = `c`.`id` WHERE `c`.`type` = 1';
        $array_catalogs = Yii::$app->db->createCommand($sql)->queryAll();
        $string_catalogs = implode(',', array_map(function ($item) {
            return array_pop($item);
        }, $array_catalogs));
        $products = CatalogBaseGoods::find()->where('id in (:string)',[':string' => $string_catalogs])->all();
        /** @var CatalogBaseGoods $product */
        foreach($products as $product) {
            $row = new CatalogGoods;
            $row->cat_id = $product->cat_id;
            $row->base_goods_id = $product->id;
            $row->price = $product->price;
            $row->vat = null;
            if (!$row->save()) {
                throw new \Exception('Не удалось сохранить для каталога ' . $product->cat_id . ' в таблице catalog_goods новую запись из catalog_base_goods ' . $product->id);
            }
        }
    }

    public function safeDown()
    {
        echo "m181116_154600_copy_products_base_catalogs_to_table_catalog_goods cannot be reverted.\n";
        return false;
    }
}
