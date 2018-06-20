<?php

use yii\db\Migration;

class m180620_091553_add_comments_table_organization_type extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `organization_type` comment "Таблица сведений о категориях организаций";');
        $this->addCommentOnColumn('{{%organization_type}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%organization_type}}', 'name', 'Название категории организаций');
    }

    public function safeDown()
    {
        $this->execute('alter table `organization_type` comment "";');
        $this->dropCommentFromColumn('{{%organization_type}}', 'id');
        $this->dropCommentFromColumn('{{%organization_type}}', 'name');
    }

}
