<?php

use yii\db\Migration;

/**
 * Class m190219_122223_add_operation_egais
 */
class m190219_122223_add_operation_egais extends Migration
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
        $this->insert("{{%all_service_operation}}", [
            "service_id" => 5,
            "code"       => 19,
            "denom"      => "setEgaisSettings",
            "comment"    => "Сохранение настроек"
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete("{{%all_service_operation}}", [
            "service_id" => 5,
            "code"       => 19,
            "denom"      => "setEgaisSettings",
            "comment"    => "Сохранение настроек"
        ]);
    }
}
