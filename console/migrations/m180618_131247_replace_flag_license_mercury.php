<?php

use yii\db\Migration;

class m180618_131247_replace_flag_license_mercury extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->update('{{%merc_service}}', array(
            'status_id' => 0),
            'status_id=1'
        );
        $this->update('{{%merc_service}}', array(
            'status_id' => 1),
            'status_id=2'
        );
    }

    public function safeDown()
    {
        $this->update('{{%merc_service}}', array(
            'status_id' => 2),
            'status_id=1'
        );
        $this->update('{{%merc_service}}', array(
            'status_id' => 1),
            'status_id=0'
        );
    }

}
