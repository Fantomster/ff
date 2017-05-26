<?php

use yii\db\Migration;

class m170525_193939_add_col_organization extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%organization}}', 'franchisee_sorted', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown() {
        $this->dropColumn('{{%organization}}', 'franchisee_sorted');
    }
}
