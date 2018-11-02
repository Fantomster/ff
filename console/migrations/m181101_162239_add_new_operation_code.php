<?php

use yii\db\Migration;

/**
 * Class m181101_162239_add_new_operation_code
 */
class m181101_162239_add_new_operation_code extends Migration
{
    public $tableName = '{{%all_service_operation}}';

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
            'service_id' => 4,
            'code' => 26,
            'denom' => 'AutoLoadMercVSDList',
            'comment' => 'Автоматическая загрузка списка ВСД',
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
