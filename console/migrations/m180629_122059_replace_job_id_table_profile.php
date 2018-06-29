<?php

use yii\db\Migration;

class m180629_122059_replace_job_id_table_profile extends Migration
{
    public function safeUp()
    {
        $this->update('{{%profile}}', array(
            'job_id' => 0),
            'job_id is null'
        );
    }

    public function safeDown()
    {
        $this->update('{{%profile}}', array(
            'job_id' => null),
            'job_id=0'
        );
    }

}
