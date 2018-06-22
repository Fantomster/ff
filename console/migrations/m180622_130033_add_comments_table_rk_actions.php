<?php

use yii\db\Migration;

class m180622_130033_add_comments_table_rk_actions extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_actions` comment "Таблица сведений о последнем запросе актуальных данных о лицензиях UCS";');
        $this->addCommentOnColumn('{{%rk_actions}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_actions}}', 'action', 'Описание действия');
        $this->addCommentOnColumn('{{%rk_actions}}', 'session', 'Сессия, во время которой делался последний запрос актуальных данных о лицензиях UCS');
        $this->addCommentOnColumn('{{%rk_actions}}', 'created', 'Дата и время, когда делался последний запрос актуальных данных о лицензиях UCS');
        $this->addCommentOnColumn('{{%rk_actions}}', 'result', 'Результат (не используется)');
        $this->addCommentOnColumn('{{%rk_actions}}', 'ip', 'ip-адрес, с которого делался последний запрос актуальных данных о лицензиях UCS');
        $this->addCommentOnColumn('{{%rk_actions}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_actions` comment "";');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'id');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'action');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'session');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'created');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'result');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'ip');
        $this->dropCommentFromColumn('{{%rk_actions}}', 'comment');
    }
}
