<?php

use yii\db\Migration;

class m181012_075216_add_comments_table_payment_type extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `payment_type` comment "Таблица сведений о типах платных услуг";');
        $this->addCommentOnColumn('{{%payment_type}}', 'type_id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%payment_type}}', 'title','Наименование типа платных услуг');
        $this->addCommentOnColumn('{{%payment_type}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%payment_type}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `payment_type` comment "";');
        $this->dropCommentFromColumn('{{%payment_type}}', 'type_id');
        $this->dropCommentFromColumn('{{%payment_type}}', 'title');
        $this->dropCommentFromColumn('{{%payment_type}}', 'created_at');
        $this->dropCommentFromColumn('{{%payment_type}}', 'updated_at');
    }
}
