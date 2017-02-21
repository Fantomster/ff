<?php

use yii\db\Migration;
use common\models\Role;
use common\models\Organization;

class m170216_082321_role_update extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%role}}', 'can_observe', $this->integer()->null()->defaultValue(0));
        $this->dropForeignKey('{{%role_org}}', '{{%role}}');
        $this->batchInsert('{{%role}}', ['id', 'name', 'can_admin', 'can_manage', 'can_observe', 'organization_type'], [
            [Role::ROLE_FRANCHISEE_OWNER, 'Владелец', 0, 0, 0, Organization::TYPE_FRANCHISEE], 
            [Role::ROLE_FRANCHISEE_OPERATOR, 'Оператор', 0, 0, 0, Organization::TYPE_FRANCHISEE],
            [Role::ROLE_FRANCHISEE_ACCOUNTANT, 'Бухгалтер', 0, 0, 0, Organization::TYPE_FRANCHISEE],
        ]);
        $this->update('{{%role}}', ['can_manage' => 1, 'can_observe' => 1], ['id' => Role::ROLE_ADMIN]);
        $this->update('{{%role}}', ['can_admin' => 0, 'can_manage' => 1, 'can_observe' => 1], ['id' => Role::ROLE_FKEEPER_MANAGER]);
        $this->update('{{%role}}', ['can_admin' => 0, 'can_manage' => 0, 'can_observe' => 1], ['id' => Role::ROLE_FKEEPER_OBSERVER]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%role}}', 'can_observe');
    }
}
