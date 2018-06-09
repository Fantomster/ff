<?php

use yii\db\Migration;

class m180607_094935_create_ooo_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%ooo}}', [
            'id' => $this->primaryKey(),
            'name_short' => $this->string(5)->null()->defaultValue(null),
            'name_long' => $this->string(100)->null()->defaultValue(null),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%ooo}}');
    }
}
