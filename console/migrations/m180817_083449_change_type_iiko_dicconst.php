<?php

use yii\db\Migration;

/**
 * Class m180817_083449_change_type_iiko_dicconst
 */
class m180817_083449_change_type_iiko_dicconst extends Migration
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
        $this->update('iiko_dicconst', ['type' => 2], "denom='main_org'");
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('iiko_dicconst', ['type' => 2], "denom='main_org'");
    }
}
