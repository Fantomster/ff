<?php

use yii\db\Migration;

class m180625_080435_add_comments_table_jobs extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `jobs` comment "Таблица сведений о должностях сотрудников поставщиков и ресторанов";');
        $this->addCommentOnColumn('{{%jobs}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%jobs}}', 'name_job', 'Наименование должности сотрудников поставщиков и ресторанов');
        $this->addCommentOnColumn('{{%jobs}}', 'organization_type_id', 'Идентификатор типа организации, к которой относится должность сотрудника');
    }

    public function safeDown()
    {
        $this->execute('alter table `jobs` comment "";');
        $this->dropCommentFromColumn('{{%jobs}}', 'id');
        $this->dropCommentFromColumn('{{%jobs}}', 'name_job');
        $this->dropCommentFromColumn('{{%jobs}}', 'organization_type_id');
    }

}
