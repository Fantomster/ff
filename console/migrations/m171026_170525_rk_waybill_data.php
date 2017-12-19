<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170525_rk_waybill_data extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%rk_waybill_data}}',
            [
                'id'=> $this->primaryKey(11),
                'waybill_id'=> $this->integer(11)->null()->defaultValue(null),
                'product_rid'=> $this->integer(11)->null()->defaultValue(null),
                'quant'=> $this->double(12, 2)->null()->defaultValue(null),
                'sum'=> $this->double(12, 2)->null()->defaultValue(null),
                'vat'=> $this->integer(11)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'munit_rid'=> $this->integer(11)->null()->defaultValue(null),
                'product_id'=> $this->integer(11)->null()->defaultValue(null),
                'koef'=> $this->double(12, 6)->null()->defaultValue(null),
                'defsum'=> $this->double(12, 2)->null()->defaultValue(null),
                'defquant'=> $this->double(12, 2)->null()->defaultValue(null),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'vat_included'=> $this->integer(11)->null()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('org-secindex','{{%rk_waybill_data}}',['org'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('org-secindex', '{{%rk_waybill_data}}');
        $this->dropTable('{{%rk_waybill_data}}');
    }
}
