<?php

use yii\db\Migration;
use yii\db\Expression;

class m180622_123428_insert_table_rk_actions extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%rk_actions}}', [
            'action' => 'Обновление данных об услугах UCS',
            'created' => new \yii\db\Expression('NOW()'),
            'ip' => '194.135.208.26',
        ]);

    }

    public function safeDown()
    {
        $this->execute('truncate table `rk_actions`;');
    }

}
