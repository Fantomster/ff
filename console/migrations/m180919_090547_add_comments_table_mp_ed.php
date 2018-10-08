<?php

use yii\db\Migration;

class m180919_090547_add_comments_table_mp_ed extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `mp_ed` comment "Таблица сведений о единицах измерения товаров";');
        $this->addCommentOnColumn('{{%mp_ed}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%mp_ed}}', 'name','Наименование единицы измерения товаров');
    }

    public function safeDown()
    {
        $this->execute('alter table `mp_ed` comment "";');
        $this->dropCommentFromColumn('{{%mp_ed}}', 'id');
        $this->dropCommentFromColumn('{{%mp_ed}}', 'name');
    }
}
