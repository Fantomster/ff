<?php

use yii\db\Migration;

class m170325_154208_tbl_request_add_coll extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%request}}', 'rest_org_id', $this->integer()->notNull());
    }

    public function safeDown() {
        $this->dropColumn('{{%request}}', 'rest_org_id');
    }
}
