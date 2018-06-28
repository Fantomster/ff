<?php

use yii\db\Migration;

class m180625_140028_create_table_gender extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%gender}}', [
            'id' => $this->primaryKey(),
            'name_gender' => $this->string(10)->null()->defaultValue(null),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('gender');
    }

}
