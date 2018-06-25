<?php

use yii\db\Migration;

class m180625_141458_create_table_allow extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%allow}}', [
            'id' => $this->primaryKey(),
            'name_allow' => $this->string(20)->null()->defaultValue(null),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('allow');
    }

}
