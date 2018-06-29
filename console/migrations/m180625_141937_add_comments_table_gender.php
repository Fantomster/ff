<?php

use yii\db\Migration;

class m180625_141937_add_comments_table_gender extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `gender` comment "Таблица наименований гендерных полов";');
        $this->addCommentOnColumn('{{%gender}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%gender}}', 'name_gender', 'Наименование гендерного пола');
    }

    public function safeDown()
    {
        $this->execute('alter table `gender` comment "";');
        $this->dropCommentFromColumn('{{%gender}}', 'id');
        $this->dropCommentFromColumn('{{%gender}}', 'name_gender');
    }

}
