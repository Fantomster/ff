<?php

use yii\db\Migration;

class m170316_131910_alter_organization_name extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%organization}}', 'name', $this->string()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%organization}}', 'name', $this->string()->notNull());
    }
}
