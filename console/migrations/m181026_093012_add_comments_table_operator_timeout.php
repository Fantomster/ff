<?php

use yii\db\Migration;

class m181026_093012_add_comments_table_operator_timeout extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `operator_timeout` comment "Таблица сведений об операторах заказов и времени ожидания ответов от них";');
        $this->addCommentOnColumn('{{%operator_timeout}}', 'operator_id', 'Идентификатор оператора заказов');
        $this->addCommentOnColumn('{{%operator_timeout}}', 'timeout_at','Текущее время в формате unix_timestamp');
        $this->addCommentOnColumn('{{%operator_timeout}}', 'timeout','Время ожидания ответа от оператора в секундах');
    }

    public function safeDown()
    {
        $this->execute('alter table `operator_timeout` comment "";');
        $this->dropCommentFromColumn('{{%operator_timeout}}', 'operator_id');
        $this->dropCommentFromColumn('{{%operator_timeout}}', 'timeout_at');
        $this->dropCommentFromColumn('{{%operator_timeout}}', 'timeout');
    }
}
