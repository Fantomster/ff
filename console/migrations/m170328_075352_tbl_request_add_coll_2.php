<?php

use yii\db\Migration;

class m170328_075352_tbl_request_add_coll_2 extends Migration
{
    public function safeUp() {
        $this->renameColumn('{{%request}}', 'updated_at', 'end');
        $this->addColumn('{{%request}}', 'active_status', $this->boolean()->notNull()->defaultValue(1));
    }

    public function safeDown() {
        $this->renameColumn('{{%request}}', 'end', 'updated_at');
        $this->dropColumn('{{%request}}', 'active_status');
    }
}
