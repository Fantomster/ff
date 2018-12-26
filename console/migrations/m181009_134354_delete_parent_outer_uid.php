<?php

use yii\db\Migration;
use common\helpers\DBNameHelper;

class m181009_134354_delete_parent_outer_uid extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropForeignKey('{{%outer_unit_org}}', '{{%outer_unit}}');
        $this->dropColumn('{{%outer_unit}}', 'parent_outer_uid');
    }

    public function safeDown()
    {
        $this->addColumn('{{%outer_unit}}', 'parent_outer_uid',
            $this->string(45)->null()->after('outer_uid')->comment('Родительский outer_id'));

        $dbName = DBNameHelper::getMainName();
        $this->addForeignKey('{{%outer_unit_org}}', '{{%outer_unit}}', 'org_id', $dbName.'.organization', 'id');
    }

}
