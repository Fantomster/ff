<?php

use yii\db\Migration;

class m181022_122050_rename_column_table_roaming_map extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%roaming_map}}', 'acquire_provuder_id', 'acquire_provider_id');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%roaming_map}}', 'acquire_provider_id', 'acquire_provuder_id');
    }
}
