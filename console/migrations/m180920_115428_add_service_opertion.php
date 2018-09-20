<?php

use yii\db\Migration;

/**
 * Class m180920_115428_add_service_opertion
 */
class m180920_115428_add_service_opertion extends Migration
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
            'code' => 25,
            'denom' => 'modifyProducerStockListOperation',
            'comment' => 'Управление номенклатурой производителя в справочнике',
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
