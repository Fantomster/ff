<?php

use yii\db\Migration;

class m180817_115357_add_comments_table_request_callback extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `request_callback` comment "Таблица сведений об ответах поставщиков на заявки ресторанов";');
        $this->addCommentOnColumn('{{%request_callback}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%request_callback}}', 'request_id','Идентификатор заявки ресторана');
        $this->addCommentOnColumn('{{%request_callback}}', 'supp_org_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%request_callback}}', 'price','Цена, предлагаемая поставщиком');
        $this->addCommentOnColumn('{{%request_callback}}', 'comment','Комментарий поставщика');
        $this->addCommentOnColumn('{{%request_callback}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%request_callback}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%request_callback}}', 'supp_user_id','Идентификатор пользователя организации-поставщика');
    }

    public function safeDown()
    {
        $this->execute('alter table `request_callback` comment "";');
        $this->dropCommentFromColumn('{{%request_callback}}', 'id');
        $this->dropCommentFromColumn('{{%request_callback}}', 'request_id');
        $this->dropCommentFromColumn('{{%request_callback}}', 'supp_org_id');
        $this->dropCommentFromColumn('{{%request_callback}}', 'price');
        $this->dropCommentFromColumn('{{%request_callback}}', 'comment');
        $this->dropCommentFromColumn('{{%request_callback}}', 'created_at');
        $this->dropCommentFromColumn('{{%request_callback}}', 'updated_at');
        $this->dropCommentFromColumn('{{%request_callback}}', 'supp_user_id');
    }
}
