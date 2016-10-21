<?php

use yii\db\Migration;

class m161021_131411_catalogs_211016 extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%goods_notes}}', 'catalog_base_goods_id', $this->string()->null());
        $this->dropColumn('{{%goods_notes}}', 'catalog_goods_id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%goods_notes}}', 'catalog_base_goods_id');
        $this->addColumn('{{%goods_notes}}', 'catalog_goods_id', $this->string()->Null());
    }
}
