<?php

use yii\db\Migration;

class m170202_142000_add_observer_role extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%role}}', ['name' => 'Наблюдатель f-keeper', 'can_admin' => 1, 'can_manage' => 0, 'organization_type' => null]);
    }

    public function safeDown()
    {
        $this->delete('{{%role}}', ['name' => 'Наблюдатель f-keeper']);
    }
}
