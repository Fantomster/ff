<?php

use yii\db\Migration;

class m161018_122114_order_changes extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%order_content}}', 'accepted_quantity', 'initial_quantity');
        $this->addColumn('{{%order}}', 'requested_delivery', $this->timestamp()->null());
        $this->addColumn('{{%order}}', 'actual_delivery', $this->timestamp()->null());
        $this->addColumn('{{%order}}', 'comment', $this->text()->null());
    }

    public function safeDown()
    {
        $this->renameColumn('{{%order_content}}', 'initial_quantity', 'accepted_quantity');
        $this->dropColumn('{{%order}}', 'requested_delivery');
        $this->dropColumn('{{%order}}', 'actual_delivery');
        $this->dropColumn('{{%order}}', 'comment');
    }
}
