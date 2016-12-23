<?php

use yii\db\Migration;

class m161223_133438_add_fields_to_organization extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'legal_entity', $this->string()->null());
        $this->addColumn('{{%organization}}', 'contact_name', $this->string()->null());
        $this->addColumn('{{%organization}}', 'about', $this->text()->null());
        $this->addColumn('{{%organization}}', 'picture', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'legal_entity');
        $this->dropColumn('{{%organization}}', 'contact_name');
        $this->dropColumn('{{%organization}}', 'about');
        $this->dropColumn('{{%organization}}', 'picture');
    }
}
