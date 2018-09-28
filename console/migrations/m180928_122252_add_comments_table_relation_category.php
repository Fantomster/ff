<?php

use yii\db\Migration;

class m180928_122252_add_comments_table_relation_category extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `relation_category` comment "Таблица сведений о связях категорий товаров, ресторанов и поставщиков";');
        $this->addCommentOnColumn('{{%relation_category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%relation_category}}', 'category_id','Идентификатор категории товаров');
        $this->addCommentOnColumn('{{%relation_category}}', 'rest_org_id','Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%relation_category}}', 'supp_org_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%relation_category}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%relation_category}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `relation_category` comment "";');
        $this->dropCommentFromColumn('{{%relation_category}}', 'id');
        $this->dropCommentFromColumn('{{%relation_category}}', 'category_id');
        $this->dropCommentFromColumn('{{%relation_category}}', 'rest_org_id');
        $this->dropCommentFromColumn('{{%relation_category}}', 'supp_org_id');
        $this->dropCommentFromColumn('{{%relation_category}}', 'created_at');
        $this->dropCommentFromColumn('{{%relation_category}}', 'updated_at');
    }
}
