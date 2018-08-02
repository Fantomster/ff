<?php

use yii\db\Migration;

class m180725_091439_alter_table_iiko_waybill extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->alterColumn('{{%iiko_waybill}}', 'num_code', $this->string(128)->notNull());
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'num_code', 'Номер документа по приходной накладной');
    }

    public function safeDown()
    {
        $this->alterColumn('{{%iiko_waybill}}', 'num_code', $this->integer()->notNull());
        $this->addCommentOnColumn('{{%iiko_waybill}}', 'num_code', 'Номер документа по приходной накладной');
    }

}
