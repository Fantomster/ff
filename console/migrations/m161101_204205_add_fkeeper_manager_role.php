<?php

use yii\db\Migration;

class m161101_204205_add_fkeeper_manager_role extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%role}}', ['name' => 'Менеджер f-keeper', 'can_admin' => 1, 'can_manage' => 0, 'organization_type' => null]);
    }

    public function safeDown()
    {
        $this->delete('{{%role}}', ['name' => 'Менеджер f-keeper']);
    }
}
