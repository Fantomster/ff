<?php

use yii\db\Migration;

class m180731_084215_add_comments_table_rk_product extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_product` comment "Таблица сведений о товарах в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_product}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_product}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_product}}', 'rid', 'Идентификатор товара в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_product}}', 'denom', 'Наименование товара');
        $this->addCommentOnColumn('{{%rk_product}}', 'cat_id', 'Идентификатор категории товара');
        $this->addCommentOnColumn('{{%rk_product}}', 'type', 'Тип (не используется');
        $this->addCommentOnColumn('{{%rk_product}}', 'comment', 'Комментарий (не используется)');
        $this->addCommentOnColumn('{{%rk_product}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_product}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_product}}', 'fproduct_id', 'Идентификатор товара в номенклатуре поставщика (не используется)');
        $this->addCommentOnColumn('{{%rk_product}}', 'group_rid', 'Идентификатор группы товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_product}}', 'group_name', 'Наименование группы товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_product}}', 'unit_rid', 'Идентификатор единицы измерения товара');
        $this->addCommentOnColumn('{{%rk_product}}', 'unitname', 'Наименование единицы измерения товара');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_product` comment "";');
        $this->dropCommentFromColumn('{{%rk_product}}', 'id');
        $this->dropCommentFromColumn('{{%rk_product}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_product}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_product}}', 'denom');
        $this->dropCommentFromColumn('{{%rk_product}}', 'cat_id');
        $this->dropCommentFromColumn('{{%rk_product}}', 'type');
        $this->dropCommentFromColumn('{{%rk_product}}', 'comment');
        $this->dropCommentFromColumn('{{%rk_product}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_product}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_product}}', 'fproduct_id');
        $this->dropCommentFromColumn('{{%rk_product}}', 'group_rid');
        $this->dropCommentFromColumn('{{%rk_product}}', 'group_name');
        $this->dropCommentFromColumn('{{%rk_product}}', 'unit_rid');
        $this->dropCommentFromColumn('{{%rk_product}}', 'unitname');
    }
}
