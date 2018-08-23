<?php

use yii\db\Migration;

/**
 * Class m180823_131259_add_comments_from_new_columns
 */
class m180823_131259_add_comments_from_new_columns extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addCommentOnColumn('{{%iiko_agent}}', 'vendor_id',
            'id поставщика mixcart');
        $this->addCommentOnColumn('{{%rk_agent}}', 'vendor_id',
            'id поставщика mixcart');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'vendor_id',
            'id поставщика mixcart');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%iiko_agent}}', 'vendor_id');
        $this->addCommentOnColumn('{{%rk_agent}}', 'vendor_id',
            'id поставщика mixcart');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'vendor_id',
            'id поставщика mixcart');
    }
}
