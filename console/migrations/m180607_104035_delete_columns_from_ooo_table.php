<?php

use yii\db\Migration;

class m180607_104035_delete_columns_from_ooo_table extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('ooo', 'created_at');
        $this->dropColumn('ooo', 'updated_at');
    }

    public function safeDown()
    {
        $this->addColumn('ooo', 'created_at', $this->timestamp()->null());
        $this->addColumn('ooo', 'updated_at', $this->timestamp()->null());
    }

}
