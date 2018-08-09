<?php

use yii\db\Migration;

/**
 * Class m180809_112630_add_new_role
 */
class m180809_112630_add_new_role extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%role}}', ['name' => 'Бухгалтер', 'can_admin' => 0, 'can_manage' => 0, 'organization_type' => 1]);
        $this->insert('{{%role}}', ['name' => 'Закупщик', 'can_admin' => 0, 'can_manage' => 0, 'organization_type' => 1]);
        $this->insert('{{%role}}', ['name' => 'Младший закупщик', 'can_admin' => 0, 'can_manage' => 0, 'organization_type' => 1]);
    }

    public function safeDown()
    {
        $this->delete('{{%role}}', ['name' => 'Бухгалтер']);
        $this->delete('{{%role}}', ['name' => 'Закупщик']);
        $this->delete('{{%role}}', ['name' => 'Младший закупщик']);
    }
}
