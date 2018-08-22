<?php

use yii\db\Migration;

class m180817_120042_add_comments_table_iiko_dic extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_dic` comment "Таблица сведений о зависимостях организаций, справочников и статусов закачки справочников в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'org_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'dictype_id','Идентификатор справочника в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'dicstatus_id','Идентификатор статуса запроса на закачку справочника в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'obj_count','Общее количество элементов справочника в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'obj_mapcount','Количество сопоставленных элементов справочника в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_dic}}', 'comment','Комментарий (не используется)');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_dic` comment "";');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'org_id');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'dictype_id');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'dicstatus_id');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'updated_at');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'obj_count');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'obj_mapcount');
        $this->dropCommentFromColumn('{{%iiko_dic}}', 'comment');
    }
}