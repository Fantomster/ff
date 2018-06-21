<?php

use yii\db\Migration;

class m180621_122100_update_td_table_rk_service extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%rk_service}}', array(
            'td' => '2020-01-01 00:00:00'),
            ''
        );

    }

    public function safeDown()
    {
        echo "m180621_122100_update_td_table_rk_service cannot be reverted.\n";

        return false;
    }

}
