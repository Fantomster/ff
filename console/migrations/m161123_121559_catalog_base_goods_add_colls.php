<?php

use yii\db\Migration;

class m161123_121559_catalog_base_goods_add_colls extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'image');
        $this->addColumn('{{%catalog_base_goods}}', 'image', $this->string()->null());
        $this->addColumn('{{%catalog_base_goods}}', 'brand', $this->string()->null());
        $this->addColumn('{{%catalog_base_goods}}', 'region', $this->string()->null());
        $this->addColumn('{{%catalog_base_goods}}', 'weight', $this->string()->null());
    }
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_base_goods}}', 'image');
        $this->dropColumn('{{%catalog_base_goods}}', 'brand');
        $this->dropColumn('{{%catalog_base_goods}}', 'region');
        $this->dropColumn('{{%catalog_base_goods}}', 'weight');
    }
}
