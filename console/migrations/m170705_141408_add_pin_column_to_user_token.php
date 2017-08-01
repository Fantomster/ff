<?php

use yii\db\Migration;

class m170705_141408_add_pin_column_to_user_token extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user_token}}','pin', $this->integer()); 
    }

    public function down()
    {
        $this->dropColumn('{{%user_token}}','pin');
        return false;
    }
}
