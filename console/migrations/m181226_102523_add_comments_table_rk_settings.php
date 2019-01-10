<?php

use yii\db\Migration;

class m181226_102523_add_comments_table_rk_settings extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_settings` comment "Таблица сведений о найстройках системы R-Keeper (не используется)";');
        $this->addCommentOnColumn('{{%rk_settings}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_settings}}', 'org', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_settings}}', 'const', 'Наименование константы настроек');
        $this->addCommentOnColumn('{{%rk_settings}}', 'value', 'Значение константы настроек');
        $this->addCommentOnColumn('{{%rk_settings}}', 'comment', 'Комментарий к константе настроек');
        $this->addCommentOnColumn('{{%rk_settings}}', 'defval', 'Значение по умолчанию константы настроек');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_settings` comment "";');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'id');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'org');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'const');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'value');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_settings}}', 'defval');
    }
}
