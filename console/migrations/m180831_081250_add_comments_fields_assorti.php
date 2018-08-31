<?php

use yii\db\Migration;

class m180831_081250_add_comments_fields_assorti extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%iiko_agent}}', 'vendor_id', 'Идентификатор поставщика Mixcart');
        $this->addCommentOnColumn('{{%rk_agent}}', 'vendor_id','Идентификатор поставщика Mixcart');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'vendor_id','Идентификатор поставщика Mixcart');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%rk_agent}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'vendor_id');
    }
}
