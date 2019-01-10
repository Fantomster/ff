<?php

use yii\db\Migration;

class m190110_111728_add_comments_table_egais_query_rests extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `egais_query_rests` comment "Таблица сведений о запросах об остатках товаров в системе ЕГАИС";');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'reply_id', 'Идентификатор запроса об остатках товаров');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'status', 'Показатель статуса подтверждённости актуальности в сравнении с базой остатков в системе ЕГАИС (0 - не потверждён, 1 - подтверждён)');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%egais_query_rests}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `egais_query_rests` comment "";');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'id');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'org_id');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'reply_id');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'status');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'created_at');
        $this->dropCommentFromColumn('{{%egais_query_rests}}', 'updated_at');
    }
}
