<?php

use yii\db\Migration;

class m181012_080719_add_comments_table_order_attachment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `order_attachment` comment "Таблица сведений о прикреплённых к заказам файлах";');
        $this->addCommentOnColumn('{{%order_attachment}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order_attachment}}', 'order_id','Идентификатор заказа, к которому относится прикреплённый файл');
        $this->addCommentOnColumn('{{%order_attachment}}', 'file','Наименование прикреплённого файла');
        $this->addCommentOnColumn('{{%order_attachment}}', 'created_at','Дата и время создания записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `order_attachment` comment "";');
        $this->dropCommentFromColumn('{{%order_attachment}}', 'id');
        $this->dropCommentFromColumn('{{%order_attachment}}', 'order_id');
        $this->dropCommentFromColumn('{{%order_attachment}}', 'file');
        $this->dropCommentFromColumn('{{%order_attachment}}', 'created_at');
    }
}
