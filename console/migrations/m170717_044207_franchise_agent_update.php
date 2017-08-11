<?php

use yii\db\Migration;
use common\models\Role;
use common\models\Organization;

class m170717_044207_franchise_agent_update extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%role}}', ['id', 'name', 'can_admin', 'can_manage', 'can_observe', 'organization_type'], [
            [Role::ROLE_FRANCHISEE_AGENT, 'Агент', 0, 0, 0, Organization::TYPE_FRANCHISEE], 
        ]);
        $this->addColumn('{{%franchisee_associate}}', 'agent_id', $this->integer()->null());
        $this->addColumn('{{%franchisee_associate}}', 'created_at', $this->timestamp()->defaultExpression("CURRENT_TIMESTAMP"));
        $this->addColumn('{{%franchisee_associate}}', 'updated_at', $this->timestamp()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%franchisee_associate}}', 'agent_id');
        $this->dropColumn('{{%franchisee_associate}}', 'created_at');
        $this->dropColumn('{{%franchisee_associate}}', 'updated_at');
    }
}
