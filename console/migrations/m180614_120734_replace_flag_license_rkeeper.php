<?php

use yii\db\Migration;

class m180614_120734_replace_flag_license_rkeeper extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%rk_service}}', array(
            'status_id' => 0),
            'status_id=1'
        );
        $this->update('{{%rk_service}}', array(
            'status_id' => 1),
            'status_id=2'
        );
    }

    public function safeDown()
    {
        $this->update('{{%rk_service}}', array(
            'status_id' => 2),
            'status_id=1'
        );
        $this->update('{{%rk_service}}', array(
            'status_id' => 1),
            'status_id=0'
        );
    }
}
