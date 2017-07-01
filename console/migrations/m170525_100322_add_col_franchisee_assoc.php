<?php

use yii\db\Migration;

class m170525_100322_add_col_franchisee_assoc extends Migration
{
    public function safeUp() {
        $this->addColumn('{{%franchisee_associate}}', 'self_registered', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown() {
        $this->dropColumn('{{%franchisee_associate}}', 'self_registered');
    }
}
