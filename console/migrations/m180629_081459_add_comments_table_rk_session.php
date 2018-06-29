<?php

use yii\db\Migration;

class m180629_081459_add_comments_table_rk_session extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    public function safeUp()
    {
        $this->execute('alter table `rk_session` comment "Таблица сведений о сессиях в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_session}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_session}}', 'fid', 'Идентификатор сессии в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_session}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_session}}', 'cook', 'Кука, используемая для авторизации');
        $this->addCommentOnColumn('{{%rk_session}}', 'rk_sessioncol', '(не используется)');
        $this->addCommentOnColumn('{{%rk_session}}', 'ip', 'ip-адрес, с которого была проведена сессия в системе R-keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_session}}', 'fd', 'Дата и время начала сессии в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_session}}', 'td', 'Дата и время окончания сессии в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_session}}', 'ver', 'Номер версии реализации сессии');
        $this->addCommentOnColumn('{{%rk_session}}', 'status', 'Показатель состояния активности сессии (0 - не активна, 1  - активна)');
        $this->addCommentOnColumn('{{%rk_session}}', 'extime', 'Ответ сервера, содержащий время на стороне сервера R-keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_session}}', 'comment', 'Комментарий к сессии (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_session` comment "";');
        $this->dropCommentFromColumn('{{%rk_session}}', 'id');
        $this->dropCommentFromColumn('{{%rk_session}}', 'fid');
        $this->dropCommentFromColumn('{{%rk_session}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_session}}', 'cook');
        $this->dropCommentFromColumn('{{%rk_session}}', 'rk_sessioncol');
        $this->dropCommentFromColumn('{{%rk_session}}', 'ip');
        $this->dropCommentFromColumn('{{%rk_session}}', 'fd');
        $this->dropCommentFromColumn('{{%rk_session}}', 'td');
        $this->dropCommentFromColumn('{{%rk_session}}', 'ver');
        $this->dropCommentFromColumn('{{%rk_session}}', 'status');
        $this->dropCommentFromColumn('{{%rk_session}}', 'extime');
        $this->dropCommentFromColumn('{{%rk_session}}', 'comment');
    }
}
