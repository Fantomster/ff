<?php

use yii\db\Migration;

class m190215_104628_add_comments_table_auth_item_child extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `auth_item_child` comment "Таблица сведений об иерархии ролей с правами доступа";');
        $this->addCommentOnColumn('{{%auth_item_child}}', 'parent', 'Название элемента-предка роли с правами доступа');
        $this->addCommentOnColumn('{{%auth_item_child}}', 'child','Название элемента-потомка роли с правами доступа');
    }

    public function safeDown()
    {
        $this->execute('alter table `auth_item_child` comment "";');
        $this->dropCommentFromColumn('{{%auth_item_child}}', 'parent');
        $this->dropCommentFromColumn('{{%auth_item_child}}', 'child');
    }
}
