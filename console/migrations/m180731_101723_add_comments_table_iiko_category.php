<?php

use yii\db\Migration;

class m180731_101723_add_comments_table_iiko_category extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_category` comment "Таблица сведений о категориях товаров в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_category}}', 'uuid', 'Уникальный идентификатор товара в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_category}}', 'parent_uuid', 'Указатель на родительский элемент в справочнике категорий товаров в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_category}}', 'denom', 'Наименование категории товара в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_category}}', 'group_type', 'Наименование группы товаров в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_category}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%iiko_category}}', 'is_active', 'Показатель статуса активности категории товара в системе IIKO (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%iiko_category}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_category}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_category` comment "";');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'uuid');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'parent_uuid');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'group_type');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'org_id');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'is_active');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_category}}', 'updated_at');
    }
}
