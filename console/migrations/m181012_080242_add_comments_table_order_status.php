<?php

use yii\db\Migration;

class m181012_080242_add_comments_table_order_status extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `order_status` comment "Таблица сведений о статусах заказов";');
        $this->addCommentOnColumn('{{%order_status}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order_status}}', 'denom','Наименование константы статуса заказа');
        $this->addCommentOnColumn('{{%order_status}}', 'comment','Псевдоним наименования статуса заказа (source_message)');
        $this->addCommentOnColumn('{{%order_status}}', 'comment_edi','Комментарий к статусу заказа, связанного с EDI');
    }

    public function safeDown()
    {
        $this->execute('alter table `order_status` comment "";');
        $this->dropCommentFromColumn('{{%order_status}}', 'id');
        $this->dropCommentFromColumn('{{%order_status}}', 'denom');
        $this->dropCommentFromColumn('{{%order_status}}', 'comment');
        $this->dropCommentFromColumn('{{%order_status}}', 'comment_edi');
    }
}
