<?php

use yii\db\Migration;

class m181012_080457_add_comments_table_order_chat extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `order_chat` comment "Таблица сведений о сообщениях в чате, связанных с заказами";');
        $this->addCommentOnColumn('{{%order_chat}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order_chat}}', 'order_id','Идентификатор заказа');
        $this->addCommentOnColumn('{{%order_chat}}', 'sent_by_id','Идентификатор пользователя, создавшего сообщение в чате');
        $this->addCommentOnColumn('{{%order_chat}}', 'is_system','Является ли сообщение созданным системой (0 - не является, 1- является)');
        $this->addCommentOnColumn('{{%order_chat}}', 'message','Текст сообщения в чате');
        $this->addCommentOnColumn('{{%order_chat}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%order_chat}}', 'viewed','Показатель статуса просмотренности сообщения в чате (0 - не просмотрено, 1 - просмотрено)');
        $this->addCommentOnColumn('{{%order_chat}}', 'recipient_id','Идентификатор пользователя, которому адресовано сообщение в чате');
        $this->addCommentOnColumn('{{%order_chat}}', 'danger','Показатель статуса важности сообщения в чате (0 - не является важным, 1 - является важным)');
    }

    public function safeDown()
    {
        $this->execute('alter table `order_chat` comment "";');
        $this->dropCommentFromColumn('{{%order_chat}}', 'id');
        $this->dropCommentFromColumn('{{%order_chat}}', 'order_id');
        $this->dropCommentFromColumn('{{%order_chat}}', 'sent_by_id');
        $this->dropCommentFromColumn('{{%order_chat}}', 'is_system');
        $this->dropCommentFromColumn('{{%order_chat}}', 'message');
        $this->dropCommentFromColumn('{{%order_chat}}', 'created_at');
        $this->dropCommentFromColumn('{{%order_chat}}', 'viewed');
        $this->dropCommentFromColumn('{{%order_chat}}', 'recipient_id');
        $this->dropCommentFromColumn('{{%order_chat}}', 'danger');
    }
}
