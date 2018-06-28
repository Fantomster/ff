<?php

use yii\db\Migration;

class m180625_075628_create_table_jobs extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%jobs}}', [
            'id' => $this->primaryKey(),
            'name_job' => $this->string(50)->null()->defaultValue(null),
            'organizarion_type_id' => $this->tinyInteger(2)->null()->defaultValue(null)
        ], $tableOptions);

    }

    public function safeDown()
    {
        $this->dropTable('jobs');
    }

}
