<?php

use yii\db\Migration;

class m171113_095500_add_column_receiving_organization extends Migration
{
    public function safeUp()
    {
        $this->addColumn('franchisee','receiving_organization', $this->integer(2));
    }

    public function safeDown()
    {
        $this->dropColumn('franchisee', 'receiving_organization');
    }
}
