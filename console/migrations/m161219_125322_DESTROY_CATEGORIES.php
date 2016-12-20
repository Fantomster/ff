<?php

use yii\db\Migration;

class m161219_125322_DESTROY_CATEGORIES extends Migration
{
    public function safeUp()
    {
        $this->update('{{%catalog_base_goods}}', ['category_id' => null]);       
    }

    public function safeDown()
    {
        //
    }
}
