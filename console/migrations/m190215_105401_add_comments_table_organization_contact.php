<?php

use yii\db\Migration;

class m190215_105401_add_comments_table_organization_contact extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `organization_contact` comment "Таблица сведений о контактах организаций";');
        $this->addCommentOnColumn('{{%organization_contact}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%organization_contact}}', 'organization_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%organization_contact}}', 'type_id','Идентификатор типа контакта (1 - электронный ящик, 2 - телефон)');
        $this->addCommentOnColumn('{{%organization_contact}}', 'contact','Телефон или электронный ящик организации');
        $this->addCommentOnColumn('{{%organization_contact}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%organization_contact}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `organization_contact` comment "";');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'id');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'organization_id');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'type_id');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'contact');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'created_at');
        $this->dropCommentFromColumn('{{%organization_contact}}', 'updated_at');
    }
}
