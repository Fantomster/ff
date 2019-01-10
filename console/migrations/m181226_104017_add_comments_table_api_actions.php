<?php

use yii\db\Migration;

class m181226_104017_add_comments_table_api_actions extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `api_actions` comment "Таблица сведений о действиях в API 1-й версии";');
        $this->addCommentOnColumn('{{%api_actions}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%api_actions}}', 'action', 'Наименование действия');
        $this->addCommentOnColumn('{{%api_actions}}', 'session', 'Номер сессии');
        $this->addCommentOnColumn('{{%api_actions}}', 'created', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%api_actions}}', 'result', 'Показатель окончания действия (0 - не сделано, 1 - сделано)');
        $this->addCommentOnColumn('{{%api_actions}}', 'comment', 'Комментарий к действию');
        $this->addCommentOnColumn('{{%api_actions}}', 'ip', 'IP-адрес, с которого поступил запрос на действие');
    }

    public function safeDown()
    {
        $this->execute('alter table `api_actions` comment "";');
        $this->dropCommentFromColumn('{{%api_actions}}', 'id');
        $this->dropCommentFromColumn('{{%api_actions}}', 'action');
        $this->dropCommentFromColumn('{{%api_actions}}', 'session');
        $this->dropCommentFromColumn('{{%api_actions}}', 'created');
        $this->dropCommentFromColumn('{{%api_actions}}', 'result');
        $this->dropCommentFromColumn('{{%api_actions}}', 'comment');
        $this->dropCommentFromColumn('{{%api_actions}}', 'ip');

    }
}
