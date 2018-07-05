<?php

use yii\db\Migration;

/**
 * Class m180705_073058_iiko_tasktype
 */
class m180705_073058_all_service_operation extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable('{{%all_service_operation}}', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer(11),
            'code' => $this->integer(11),
            'denom' => $this->string(120),
            'comment' => $this->text()
        ]);

        $this->createIndex('ix_service', '{{%all_service_operation}}', 'service_id');
        $this->createIndex('ix_service_operation', '{{%all_service_operation}}', ['service_id', 'code']);
        $this->addForeignKey('fk_service', '{{%all_service_operation}}', 'service_id', 'all_service', 'id');

        //Добавляем существующие операции для R-keeper
        $operations = $this->db->createCommand("SELECT 1 as service_id, code, denom, comment FROM rk_tasktype")->queryAll();
        $this->batchInsert('{{%all_service_operation}}', ['service_id', 'code', 'denom', 'comment'], $operations);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_service', '{{%all_service_operation}}');
        $this->dropIndex('ix_service', '{{%all_service_operation}}');
        $this->dropIndex('ix_service_operation', '{{%all_service_operation}}');
        $this->dropTable('{{%all_service_operation}}');
    }
}
