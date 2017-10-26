<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170459_api_units_rus_v extends Migration
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
            '{{%api_units_rus_v}}',
            [
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'denom'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%api_units_rus_v}}');
    }
}
