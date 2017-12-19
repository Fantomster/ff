<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170524_rk_waybill extends Migration
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
            '{{%rk_waybill}}',
            [
                'id'=> $this->primaryKey(11),
                'order_id'=> $this->integer(11)->null()->defaultValue(null),
                'doc_date'=> $this->datetime()->null()->defaultValue(null),
                'corr_rid'=> $this->integer(11)->null()->defaultValue(null),
                'store_rid'=> $this->integer(11)->null()->defaultValue(null),
                'active'=> $this->integer(11)->null()->defaultValue(null),
                'note'=> $this->string(255)->null()->defaultValue(null),
                'text_code'=> $this->string(128)->null()->defaultValue(null),
                'num_code'=> $this->integer(11)->null()->defaultValue(null),
                'is_duedate'=> $this->integer(11)->null()->defaultValue(null),
                'status_id'=> $this->integer(11)->null()->defaultValue(null),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'exported_at'=> $this->datetime()->null()->defaultValue(null),
                'readytoexport'=> $this->integer(11)->null()->defaultValue(null),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'vat_included'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_waybill}}');
    }
}
