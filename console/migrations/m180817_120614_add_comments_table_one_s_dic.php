<?php

use yii\db\Migration;

class m180817_120614_add_comments_table_one_s_dic extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `one_s_dic` comment "Таблица сведений о зависимостях организаций, справочников и статусов закачки справочников в системе 1C";');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'org_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'dictype_id','Идентификатор справочника в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'dicstatus_id','Идентификатор статуса запроса на закачку справочника в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'obj_count','Общее количество элементов справочника в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'obj_mapcount','Количество сопоставленных элементов справочника в системе 1C');
        $this->addCommentOnColumn('{{%one_s_dic}}', 'comment','Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `one_s_dic` comment "";');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'id');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'org_id');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'dictype_id');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'dicstatus_id');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'created_at');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'updated_at');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'obj_count');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'obj_mapcount');
        $this->dropCommentFromColumn('{{%one_s_dic}}', 'comment');
    }
}
