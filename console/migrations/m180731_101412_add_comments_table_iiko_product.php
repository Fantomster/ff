<?php

use yii\db\Migration;

class m180731_101412_add_comments_table_iiko_product extends Migration

{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_product` comment "Таблица сведений о товарах в системе IIKO";');
        $this->addCommentOnColumn('{{%iiko_product}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%iiko_product}}', 'uuid', 'Уникальный идентификатор продукта в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'denom', 'Наименование товара');
        $this->addCommentOnColumn('{{%iiko_product}}', 'parent_uuid', 'Указатель на родительский элемент в справочнике категорий товаров в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'org_id', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%iiko_product}}', 'num', 'Артикул в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'code', 'Код быстрого набора в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'product_type', 'Тип элемента номенклатуры в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'cooking_place_type', 'Тип места приготовления продукта');
        $this->addCommentOnColumn('{{%iiko_product}}', 'unit', 'Единица измерения товара в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'containers', 'Поле для массивов сериализации содержимых товаров (не используется)');
        $this->addCommentOnColumn('{{%iiko_product}}', 'is_active', 'Показатель состояния активности товара в системе IIKO');
        $this->addCommentOnColumn('{{%iiko_product}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%iiko_product}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `iiko_product` comment "";');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'id');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'uuid');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'denom');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'parent_uuid');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'org_uuid');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'num');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'code');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'product_type');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'cooking_place_type');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'unit');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'containers');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'is_active');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'created_at');
        $this->dropCommentFromColumn('{{%iiko_product}}', 'updated_at');

    }
}

