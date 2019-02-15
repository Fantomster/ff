<?php

use yii\db\Migration;

class m190215_110013_add_comments_table_promo_action extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `promo_action` comment "Таблица сведений о промоакциях";');
        $this->addCommentOnColumn('{{%promo_action}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%promo_action}}', 'name', 'Название промоакции');
        $this->addCommentOnColumn('{{%promo_action}}', 'code', 'Код промоакции');
        $this->addCommentOnColumn('{{%promo_action}}', 'title', 'Заголовок в сообщении о промоакции');
        $this->addCommentOnColumn('{{%promo_action}}', 'message', 'Содержание сообщения о промоакции');
        $this->addCommentOnColumn('{{%promo_action}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%promo_action}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `promo_action` comment "";');
        $this->dropCommentFromColumn('{{%promo_action}}', 'id');
        $this->dropCommentFromColumn('{{%promo_action}}', 'name');
        $this->dropCommentFromColumn('{{%promo_action}}', 'code');
        $this->dropCommentFromColumn('{{%promo_action}}', 'title');
        $this->dropCommentFromColumn('{{%promo_action}}', 'message');
        $this->dropCommentFromColumn('{{%promo_action}}', 'created_at');
        $this->dropCommentFromColumn('{{%promo_action}}', 'updated_at');
    }
}
