<?php

use yii\db\Migration;

class m181026_094340_add_comments_table_all_service_operation extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `all_service_operation` comment "Таблица сведений о типах операций в учётных системах";');
        $this->addCommentOnColumn('{{%all_service_operation}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%all_service_operation}}', 'service_id', 'Идентификатор учётного сервиса (таблица all_service)');
        $this->addCommentOnColumn('{{%all_service_operation}}', 'code', 'Код операции');
        $this->addCommentOnColumn('{{%all_service_operation}}', 'denom', 'slug-псевдоним операции');
        $this->addCommentOnColumn('{{%all_service_operation}}', 'comment', 'Комментарий, описание сути операции');
    }

    public function safeDown()
    {
        $this->execute('alter table `all_service_operation` comment "";');
        $this->dropCommentFromColumn('{{%all_service_operation}}', 'id');
        $this->dropCommentFromColumn('{{%all_service_operation}}', 'service_id');
        $this->dropCommentFromColumn('{{%all_service_operation}}', 'code');
        $this->dropCommentFromColumn('{{%all_service_operation}}', 'denom');
        $this->dropCommentFromColumn('{{%all_service_operation}}', 'comment');
    }
}
