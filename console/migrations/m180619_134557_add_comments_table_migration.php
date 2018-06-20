<?php

use yii\db\Migration;

class m180619_134557_add_comments_table_migration extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `migration` comment "Таблица сведений о реализованных миграциях";');
        $this->addCommentOnColumn('{{%migration}}', 'version', 'Наименование (версия) миграции с указанием даты, времени и краткого объяснения');
        $this->addCommentOnColumn('{{%migration}}', 'apply_time', 'Время реализации миграции в unixtime');
    }

    public function safeDown()
    {
        $this->execute('alter table `migration` comment "";');
        $this->dropCommentFromColumn('{{%migration}}', 'version');
        $this->dropCommentFromColumn('{{%migration}}', 'apply_time');
    }

}
