<?php

use yii\db\Migration;

class m181214_113521_add_comments_table_cart extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `cart` comment "Таблица сведений о корзинах отобранных товаров";');
        $this->addCommentOnColumn('{{%cart}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%cart}}', 'organization_id','Идентификатор организации, чей сотрудник создал корзину');
        $this->addCommentOnColumn('{{%cart}}', 'user_id','Идентификатор пользователя, создавшего корзину');
        $this->addCommentOnColumn('{{%cart}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%cart}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `cart` comment "";');
        $this->dropCommentFromColumn('{{%cart}}', 'id');
        $this->dropCommentFromColumn('{{%cart}}', 'organization_id');
        $this->dropCommentFromColumn('{{%cart}}', 'user_id');
        $this->dropCommentFromColumn('{{%cart}}', 'created_at');
        $this->dropCommentFromColumn('{{%cart}}', 'updated_at');
    }
}
