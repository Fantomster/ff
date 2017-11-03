<?php

use yii\db\Migration;

/**
 * Handles the creation of table `api_settings`.
 */
class m171030_173622_create_api_settings_table extends Migration
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
            '{{%rk_settings}}',
            [
                'id'=> $this->primaryKey(11),
                'org'=> $this->integer(11)->null()->defaultValue(null),
                'const'=> $this->string(255)->null()->defaultValue(null),
                'value'=> $this->string(255)->null()->defaultValue(null),
                'comment' => $this->string(255)->null()->defaultValue(null),

            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%rk_settings}}');
    }
}
