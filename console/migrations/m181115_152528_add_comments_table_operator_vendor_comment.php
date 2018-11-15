<?php

use yii\db\Migration;

class m181115_152528_add_comments_table_operator_vendor_comment extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `operator_vendor_comment` comment "Таблица комментариев сотрудников Mixcarta о поставщиках";');
        $this->addCommentOnColumn('{{%operator_vendor_comment}}', 'vendor_id', 'Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%operator_vendor_comment}}', 'comment','Комментарий сотрудников Mixcarta о поставщике');
        $this->addCommentOnColumn('{{%operator_vendor_comment}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%operator_vendor_comment}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `operator_vendor_comment` comment "";');
        $this->dropCommentFromColumn('{{%operator_vendor_comment}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%operator_vendor_comment}}', 'comment');
        $this->dropCommentFromColumn('{{%operator_vendor_comment}}', 'created_at');
        $this->dropCommentFromColumn('{{%operator_vendor_comment}}', 'updated_at');
    }
}
