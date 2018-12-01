<?php

use yii\db\Migration;

class m181130_121207_add_comments_table_edi_order extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_order` comment "Таблица сведений о связях заказов с документами EDI";');
        $this->addCommentOnColumn('{{%edi_order}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_order}}', 'order_id','Идентификатор заказа');
        $this->addCommentOnColumn('{{%edi_order}}', 'invoice_number','Номер счёта-фактуры, связанного с заказом');
        $this->addCommentOnColumn('{{%edi_order}}', 'invoice_date','Дата счёта-фактуры, связанного с заказом');
        $this->addCommentOnColumn('{{%edi_order}}', 'lang','Двухбуквенное обозначение языка, на котором сделан заказ');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_order` comment "";');
        $this->dropCommentFromColumn('{{%edi_order}}', 'id');
        $this->dropCommentFromColumn('{{%edi_order}}', 'order_id');
        $this->dropCommentFromColumn('{{%edi_order}}', 'invoice_number');
        $this->dropCommentFromColumn('{{%edi_order}}', 'invoice_date');
        $this->dropCommentFromColumn('{{%edi_order}}', 'lang');
    }
}