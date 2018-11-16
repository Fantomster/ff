<?php

use yii\db\Migration;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use yii\helpers\ArrayHelper;

class m181116_154600_copy_products_base_catalogs_to_table_catalog_goods extends Migration
{
    public function safeUp()
    {
        $sql = 'SELECT DISTINCT `cat_id` FROM `relation_supp_rest` `rsr` LEFT JOIN `catalog` `c` ON `rsr`.`cat_id` = `c`.`id` WHERE `c`.`type` = 1';
        $array_catalogs = Yii::$app->db->createCommand($sql)->queryAll();
        $array_1 = ArrayHelper::getColumn($array_catalogs, 'cat_id');
        $products = CatalogBaseGoods::find()->where(['cat_id' => $array_1])->andWhere(['deleted' => 0])->all();
        /** @var CatalogBaseGoods $product */
        foreach ($products as $product) {
            $result = CatalogGoods::find()->where(['base_goods_id' => $product->id])->exists();
            if ($result === false) {
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
    }

    public function safeDown()
    {
        echo "m181116_154600_copy_products_base_catalogs_to_table_catalog_goods cannot be reverted.\n";
        return false;
    }
}
