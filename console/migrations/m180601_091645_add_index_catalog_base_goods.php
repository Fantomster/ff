<?php

use yii\db\Migration;

class m180601_091645_add_index_catalog_base_goods extends Migration
{

    public function safeUp()
    {
        $table = \common\models\CatalogBaseGoods::tableName();
        $this->createIndex('cbg_supp_org_id_product', $table, ['supp_org_id', 'product']);
        $this->createIndex('cbg_product', $table, 'product');
    }

    public function safeDown()
    {
        $table = \common\models\CatalogBaseGoods::tableName();
        $this->dropIndex('cbg_supp_org_id_product', $table);
        $this->dropIndex('cbg_product', $table);
    }
}
