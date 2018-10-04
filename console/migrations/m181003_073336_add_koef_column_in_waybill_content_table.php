<?php

use yii\db\Migration;

/**
 * Class m181003_073336_add_koef_column_in_waybill_content_table
 */
class m181003_073336_add_koef_column_in_waybill_content_table extends Migration
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
        $this->addColumn('{{%waybill_content}}', 'koef', $this->float()->null());
        $this->addCommentOnColumn('{{%waybill_content}}', 'koef', 'коэффициент');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%waybill_content}}', 'koef');
    }
}
