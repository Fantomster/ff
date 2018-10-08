<?php

use yii\db\Migration;


class m181005_082332_add_outer_dictionary_values extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%outer_dictionary}}', ['name' => 'agent', 'service_id' => 1]);
        $this->insert('{{%outer_dictionary}}', ['name' => 'category', 'service_id' => 1]);
        $this->insert('{{%outer_dictionary}}', ['name' => 'product', 'service_id' => 1]);
        $this->insert('{{%outer_dictionary}}', ['name' => 'unit', 'service_id' => 1]);
        $this->insert('{{%outer_dictionary}}', ['name' => 'store', 'service_id' => 1]);
    }

    public function safeDown()
    {
        $this->delete('{{%outer_dictionary}}', ['service_id' => 1]);
    }

}
