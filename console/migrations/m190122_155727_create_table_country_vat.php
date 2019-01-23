<?php

use yii\db\Migration;

class m190122_155727_create_table_country_vat extends Migration
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
            '{{%country_vat}}',
            [
                'id'=> $this->primaryKey(11),
                'uuid'=> $this->string(36)->null()->defaultValue(null),
                'vats'=> $this->string()->null()->defaultValue(null),
                'created_at'=> $this->timestamp()->null()->defaultValue(null),
                'updated_at'=> $this->timestamp()->null()->defaultValue(null),
                'created_by_id'=> $this->integer(11)->null()->defaultValue(null),
                'updated_by_id'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%country_vat}}');
    }
}
