<?php

use yii\db\Migration;

class m181012_081141_add_comments_table_network_organization extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `network_organization` comment "Таблица сведений о подчинённости организаций";');
        $this->addCommentOnColumn('{{%network_organization}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%network_organization}}', 'organization_id','Идентификатор организации, являющейся подчинённой');
        $this->addCommentOnColumn('{{%network_organization}}', 'parent_id','Идентификатор "родительской" организации');
        $this->addCommentOnColumn('{{%network_organization}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%network_organization}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `network_organization` comment "";');
        $this->dropCommentFromColumn('{{%network_organization}}', 'id');
        $this->dropCommentFromColumn('{{%network_organization}}', 'organization_id');
        $this->dropCommentFromColumn('{{%network_organization}}', 'parent_id');
        $this->dropCommentFromColumn('{{%network_organization}}', 'created_at');
        $this->dropCommentFromColumn('{{%network_organization}}', 'updated_at');
    }
}
