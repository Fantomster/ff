<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170501_migration extends Migration
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
            '{{%migration}}',
            [
                'version'=> $this->string(180)->notNull(),
                'apply_time'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_migration','{{%migration}}',['version']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_migration','{{%migration}}');
        $this->dropTable('{{%migration}}');
    }
}
