<?php

use yii\db\Migration;

/**
 * Class m180822_083839_add_comments_to_columns_rk_and_iiko_waybilldata_tables
 */
class m180822_083839_add_comments_to_columns_rk_and_iiko_waybilldata_tables extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'unload_status', 'Статус для выгрузки товара в накладную, 0 - не выгружать');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'unload_status', 'Статус для выгрузки товара в накладную, 0 - не выгружать');
        
    }
    
    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%rk_waybill_data}}', 'unload_status');
        $this->dropCommentFromColumn('{{%iiko_waybill_data}}', 'unload_status');
    }
}
