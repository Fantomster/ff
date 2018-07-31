<?php

use yii\db\Migration;

class m180731_102246_add_comments_table_rk_dic extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_dic` comment "Таблица сведений о зависимостях организаций, справочников и статусов закачки справочников в системе R-Keeper";');
        $this->addCommentOnColumn('{{%rk_dic}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_dic}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_dic}}', 'dictype_id', 'Идентификатор справочника в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_dic}}', 'dicstatus_id', 'Идентификатор статуса запроса на закачку справочника в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_dic}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_dic}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_dic}}', 'obj_count', 'Общее количество элементов справочника в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_dic}}', 'obj_mapcount', 'Количество сопоставленных элементов справочника в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_dic}}', 'comment', 'Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_dic` comment "";');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'id');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'org_id');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'dictype_id');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'dicstatus_id');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'obj_count');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'obj_mapcount');
        $this->dropCommentFromColumn('{{%rk_dic}}', 'comment');
    }
}
