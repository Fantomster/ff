<?php

use yii\db\Migration;

/**
 * Class m181206_080231_add_index_order_chat
 */
class m181206_080231_add_index_order_chat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_recipient_id', \common\models\OrderChat::tableName(), 'recipient_id');
        $this->addForeignKey('fk_recipient_id_org_id',
            \common\models\OrderChat::tableName(),
            'recipient_id',
            \common\models\Organization::tableName(),
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_recipient_id_org_id', \common\models\OrderChat::tableName());
        $this->dropIndex('idx_recipient_id', \common\models\OrderChat::tableName());
    }
}
