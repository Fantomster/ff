<?php

use yii\db\Migration;

/**
 * Class m180822_093927_add_status_column_and_comment_for_1c_data
 */
class m180822_093927_add_status_column_and_comment_for_1c_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%one_s_waybill_data}}', 'unload_status', $this->integer()->notNull()->defaultValue(1));
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'unload_status', 'Статус для выгрузки товара в накладную, 0 - не выгружать');
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%one_s_waybill_data}}', 'unload_status');
        $this->dropCommentFromColumn('{{%one_s_waybill_data}}', 'unload_status');
    }
}
