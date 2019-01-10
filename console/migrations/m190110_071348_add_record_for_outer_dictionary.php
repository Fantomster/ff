<?php

use yii\db\Migration;

class m190110_071348_add_record_for_outer_dictionary extends Migration
{
    public $tableName = '{{%outer_dictionary}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->batchInsert($this->tableName, [
            'name',
            'service_id'
        ], [
            ['agent', 10],
            ['product', 10],
            ['unit', 10],
            ['store', 10],
        ]);
    }

    public function safeDown()
    {
        $this->delete($this->tableName, [
            'service_id' => 10,
            'name'       => [
                'agent',
                'product',
                'unit',
                'store'
            ]
        ]);
    }
}
