<?php
use yii\db\Schema;
use yii\db\Migration;

class m161110_153005_counter extends Migration
{
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%main_counter}}', [
            'id' => Schema::TYPE_PK,
            'rest_count' => Schema::TYPE_INTEGER . ' NOT NULL',
            'supp_count' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->insert('{{%main_counter}}', ['rest_count' => 400, 'supp_count' => 200]);
    }

    public function safeDown() {
        $this->dropTable('{{%main_counter}}');
    }
}
