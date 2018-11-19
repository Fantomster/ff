<?php

use yii\db\Migration;
use common\models\Catalog;
use yii\helpers\ArrayHelper;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;

class m181119_112051_copy_products_base_catalogs_to_table_catalog_goods2 extends Migration
{
    public function safeUp()
    {
        {
            $array_catalogs = Catalog::find()->where(['type' => 1])->all();
            $array_1 = ArrayHelper::getColumn($array_catalogs, 'id');
            unset($array_catalogs);
            foreach ($array_1 as $cat) {
                $products = CatalogBaseGoods::find()->where(['cat_id' => $cat])->andWhere(['deleted' => 0])->all();
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
        }
    }

    public function safeDown()
    {
        echo "m181119_112051_copy_products_base_catalogs_to_table_catalog_goods2 cannot be reverted.\n";
        return false;
    }
}
