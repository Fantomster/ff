<?php

use yii\db\Migration;

class m181012_080942_add_comments_table_order_assignment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `order_assignment` comment "Таблица сведений о назначенных заказах";');
        $this->addCommentOnColumn('{{%order_assignment}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%order_assignment}}', 'order_id','Идентификатор заказа');
        $this->addCommentOnColumn('{{%order_assignment}}', 'assigned_to','Идентификатор пользователя, кому назначен заказ');
        $this->addCommentOnColumn('{{%order_assignment}}', 'assigned_by','Идентификатор пользователя, кем назначен заказ');
        $this->addCommentOnColumn('{{%order_assignment}}', 'is_processed','Показатель состояния обработки заказа (0 - не обработан, 1 - обработан)');
        $this->addCommentOnColumn('{{%order_assignment}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%order_assignment}}', 'processed_at','Дата и время обработки заказа');
    }

    public function safeDown()
    {
        $this->execute('alter table `order_assignment` comment "";');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'id');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'order_id');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'assigned_to');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'assigned_by');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'is_processed');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'created_at');
        $this->dropCommentFromColumn('{{%order_assignment}}', 'processed_at');
    }
}
