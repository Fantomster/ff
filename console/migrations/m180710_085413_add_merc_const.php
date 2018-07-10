<?php

use yii\db\Migration;

/**
 * Class m180710_085413_add_merc_const
 */
class m180710_085413_add_merc_const extends Migration
{
    public $tableName = '{{%merc_dicconst}}';

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
        $this->insert($this->tableName, [
            'denom' => 'hand_load_only',
            'def_value' => '0',
            'comment' => 'Только ручная загрузка ВСД',
            'type' => 4,
            'is_active' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180511_104644_add_const_in_merc_dictconst cannot be reverted.\n";

        return false;
    }
}
