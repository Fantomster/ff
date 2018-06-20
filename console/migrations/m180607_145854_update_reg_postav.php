<?php

use yii\db\Migration;

class m180607_145854_update_reg_postav extends Migration
{

    public function safeUp()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 0),
            'id=16'
        );
    }

    public function safeDown()
    {
        $this->update('{{%integration_torg12_columns}}', array(
            'regular_expression' => 1),
            'id=16'
        );
    }
}
