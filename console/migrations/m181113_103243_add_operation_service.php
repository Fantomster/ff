<?php

use yii\db\Migration;

/**
 * Class m181113_103243_add_operation_service
 */
class m181113_103243_add_operation_service extends Migration
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
            'code' => 27,
            'denom' => 'checkShipmentRegionalizationOperation',
            'comment' => 'Проверка регионализации',
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
