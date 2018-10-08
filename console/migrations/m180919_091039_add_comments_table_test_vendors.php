<?php

use yii\db\Migration;

class m180919_091039_add_comments_table_test_vendors extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `test_vendors` comment "Таблица сведений о шаблонах закупок для поставщиков";');
        $this->addCommentOnColumn('{{%test_vendors}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%test_vendors}}', 'vendor_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%test_vendors}}', 'guide_name','Наименование шаблона закупок');
        $this->addCommentOnColumn('{{%test_vendors}}', 'is_active','Показатель состояния активности (0 - не активно, 1 - активно)');
    }

    public function safeDown()
    {
        $this->execute('alter table `test_vendors` comment "";');
        $this->dropCommentFromColumn('{{%test_vendors}}', 'id');
        $this->dropCommentFromColumn('{{%test_vendors}}', 'vendor_id');
        $this->dropCommentFromColumn('{{%test_vendors}}', 'guide_name');
        $this->dropCommentFromColumn('{{%test_vendors}}', 'is_active');
    }
}
