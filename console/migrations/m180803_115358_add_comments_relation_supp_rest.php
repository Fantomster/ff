<?php

use yii\db\Migration;

class m180803_115358_add_comments_relation_supp_rest extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `relation_supp_rest` comment "Таблица сведений о связях каталогах товаров поставщиков и ресторанов";');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'rest_org_id','Идентификатор организации-ресторана');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'supp_org_id','Идентификатор организации-поставщика');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'cat_id','Идентификатор каталога товаров');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'invite','Показатель наличия связи с поставщиком (0 - нет связи, 1 - есть связь)');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'status','Показатель состояния активности каталога (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'uploaded_catalog','Название файла, содержащего каталог');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'uploaded_processed','Показатель состояния внедрения каталога в систему (0 - каталог не внедрён, 1 - каталог внедрён)');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'is_from_market','Показатель состояния получения каталога из Маркета (0 - получен не из Маркета, 1 - получен из Маркета)');
        $this->addCommentOnColumn('{{%relation_supp_rest}}', 'deleted','Показатель состояния удаления каталога (0 - не удалён, 1 - удалён)');
    }

    public function safeDown()
    {
        $this->execute('alter table `relation_supp_rest` comment "";');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'id');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'rest_org_id');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'supp_org_id');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'cat_id');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'invite');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'created_at');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'updated_at');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'status');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'uploaded_catalog');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'uploaded_processed');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'is_from_market');
        $this->dropCommentFromColumn('{{%relation_supp_rest}}', 'deleted');
    }
}
