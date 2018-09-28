<?php

use yii\db\Migration;

class m180928_120726_add_comments_table_request_counters extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `request_counters` comment "Таблица сведений о просмотрах заявок ресторанов пользователями";');
        $this->addCommentOnColumn('{{%request_counters}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%request_counters}}', 'request_id','Идентификатор заявки ресторана');
        $this->addCommentOnColumn('{{%request_counters}}', 'user_id','Идентификатор пользователя, просмотревшего заявку');
        $this->addCommentOnColumn('{{%request_counters}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%request_counters}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `request_counters` comment "";');
        $this->dropCommentFromColumn('{{%request_counters}}', 'id');
        $this->dropCommentFromColumn('{{%request_counters}}', 'request_id');
        $this->dropCommentFromColumn('{{%request_counters}}', 'user_id');
        $this->dropCommentFromColumn('{{%request_counters}}', 'created_at');
        $this->dropCommentFromColumn('{{%request_counters}}', 'updated_at');
    }
}
