<?php

use yii\db\Migration;


class m180615_100934_add_comments_table_rk_dictype extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    public function safeUp()
    {
        $this->execute('alter table `rk_dictype` comment "Таблица сведений о справочниках в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'denom', 'Наименование справочника в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'comment', 'Комментарий (не используется)');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'contr', 'Название контроллера, вызываемого при загрузке справочника');
        $this->execute('alter table `rk_service` comment "Таблица сведений о лицензиях UCS в системе R-keeper";');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_service` comment "";');
        $this->execute('alter table `rk_dictype` comment "";');
        $this->dropCommentFromColumn('{{%rk_dictype}}', 'id');
        $this->dropCommentFromColumn('{{%rk_dictype}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_dictype}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_dictype}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_dictype}}', 'contr');
    }


}
