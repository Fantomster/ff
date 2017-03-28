<?php

use yii\db\Migration;

class m170320_084634_client_management_for_vendors extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->update('{{%role}}', ["name" => 'Руководитель'], ['id' => 5]);
        $this->update('{{%role}}', ["name" => 'Менеджер'], ['id' => 6]);
        $this->addColumn('{{%relation_supp_rest}}', 'vendor_manager_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->update('{{%role}}', ["name" => 'Менеджер'], ['id' => 5]);
        $this->update('{{%role}}', ["name" => 'Работник'], ['id' => 6]);
        $this->dropColumn('{{%relation_supp_rest}}', 'vendor_manager_id');
    }
}
