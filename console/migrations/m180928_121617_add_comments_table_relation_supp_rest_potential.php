<?php

use yii\db\Migration;

class m180928_121617_add_comments_table_relation_supp_rest_potential extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `relation_supp_rest_potential` comment "Таблица сведений о связях каталогов товаров потенциальных поставщиков и ресторанов";');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'id', 'Идентификатор записи в таблице','');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'rest_org_id','Идентификатор организации-ресторана, отправившему приглашение');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'supp_org_id','Идентификатор организации-поставщика, которой отправлено приглашение');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'cat_id','Идентификатор каталога товаров');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'invite','Показатель наличия связи с поставщиком (0 - нет связи, 1 - есть связь)');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'uploaded_catalog','Название файла, содержащего каталог');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'uploaded_processed','Показатель состояния внедрения каталога в систему (0 - каталог не внедрён, 1 - каталог внедрён)');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'status','Показатель состояния активности каталога (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'is_from_market','Показатель состояния получения каталога из Маркета (0 - получен не из Маркета, 1 - получен из Маркета)');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'deleted','Показатель состояния удаления каталога (0 - не удалён, 1 - удалён)');
        $this->addCommentOnColumn('{{%relation_supp_rest_potential}}', 'supp_user_id','Идентификатор пользователя организации-поставщика');
    }

    public function safeDown()
    {
        $this->execute('alter table `relation_supp_rest_potential` comment "";');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'id','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'rest_org_id','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'supp_org_id','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'cat_id','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'invite','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'created_at','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'updated_at','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'uploaded_catalog','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'uploaded_processed','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'status','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'is_from_market','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'deleted','');
        $this->dropCommentFromColumn('{{%relation_supp_rest_potential}}', 'supp_user_id','');
    }
}
