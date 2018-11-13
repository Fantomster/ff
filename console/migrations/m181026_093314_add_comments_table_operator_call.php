<?php

use yii\db\Migration;

class m181026_093314_add_comments_table_operator_call extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `operator_call` comment "Таблица сведений об операторах заказов";');
        $this->addCommentOnColumn('{{%operator_call}}', 'order_id', 'Идентификатор заказа');
        $this->addCommentOnColumn('{{%operator_call}}', 'operator_id','Идентификатор оператора заказа');
        $this->addCommentOnColumn('{{%operator_call}}', 'status_call_id','Идентификатор статуса звонка (1 - открыто, 2 - перезвонить, 3 - завершено)');
        $this->addCommentOnColumn('{{%operator_call}}', 'comment','Комментарий к звонку');
        $this->addCommentOnColumn('{{%operator_call}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%operator_call}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%operator_call}}', 'closed_at','Дата и время завершения звонка');
    }

    public function safeDown()
    {
        $this->execute('alter table `operator_call` comment "";');
        $this->dropCommentFromColumn('{{%operator_call}}', 'order_id');
        $this->dropCommentFromColumn('{{%operator_call}}', 'operator_id');
        $this->dropCommentFromColumn('{{%operator_call}}', 'status_call_id');
        $this->dropCommentFromColumn('{{%operator_call}}', 'comment');
        $this->dropCommentFromColumn('{{%operator_call}}', 'created_at');
        $this->dropCommentFromColumn('{{%operator_call}}', 'updated_at');
        $this->dropCommentFromColumn('{{%operator_call}}', 'closed_at');
    }
}
