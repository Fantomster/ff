<?php

use yii\db\Migration;

/**
 * Class m180813_102324_add_const_to_iiko_dict
 */
class m180813_102324_add_const_to_iiko_dict extends Migration
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
        $this->insert('{{%iiko_dicconst}}', ['denom' => 'main_org', 'def_value' => 0, 'comment' => 'Бизнес для сопоставления', 'type' => 6, 'is_active' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%iiko_dicconst}}', ['denom' => 'main_org']);
    }
}
