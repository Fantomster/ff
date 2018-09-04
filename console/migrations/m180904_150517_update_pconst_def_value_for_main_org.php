<?php

use yii\db\Migration;

/**
 * Class m180904_150517_update_pconst_def_value_for_main_org
 */
class m180904_150517_update_pconst_def_value_for_main_org extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%iiko_dicconst}}', ['def_value' => NULL], ['denom' => 'main_org']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('{{%iiko_dicconst}}', ['def_value' => 0], ['denom' => 'main_org']);
    }
}
