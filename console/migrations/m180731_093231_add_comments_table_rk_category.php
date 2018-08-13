<?php

use yii\db\Migration;

class m180731_093231_add_comments_table_rk_category extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_category` comment "Таблица сведений о категориях товаров в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_category}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_category}}', 'root', 'Идентификатор корневого элемента справочника категорий товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'rid', 'Идентификатор товара в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'lft', 'Указатель на "левый" элемент в справочнике категорий товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'rgt', 'Указатель на "правый" элемент в справочнике категорий товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'lvl', 'Показатель поколения элемента в справочнике категорий товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'prnt', 'Указатель на родительский элемент в справочнике категорий товаров в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'name', 'Наименование категории товара в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'icon', 'Ссылка на иконку категории товара в системе R-Keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_category}}', 'icon_type', 'Тип файла иконки категории товара в системе R-Keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_category}}', 'active', 'Показатель статуса активности категории товара в системе R-Keeper (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%rk_category}}', 'selected', 'Показатель статуса отмеченности категории товара в системе R-Keeper (0 - не отмечен, 1 - активен)');
        $this->addCommentOnColumn('{{%rk_category}}', 'disabled', 'Показатель статуса выключенности категории товара в системе R-Keeper (0 - не выключен, 1 - выключен)');
        $this->addCommentOnColumn('{{%rk_category}}', 'readonly', 'Показатель статуса возможности изменения категории товара в системе R-Keeper (0 - не изменяется, 1 - изменяется)');
        $this->addCommentOnColumn('{{%rk_category}}', 'visible', 'Показатель статуса видимости категории товара в системе R-Keeper (0 - не видим, 1 - видим)');
        $this->addCommentOnColumn('{{%rk_category}}', 'collapsed', 'Показатель статуса свёрнутости категории товара в системе R-Keeper (0 - не свёрнут, 1 - свёрнут)');
        $this->addCommentOnColumn('{{%rk_category}}', 'movable_u', 'Показатель статуса возможности перемещения в виджете "вверх" категории товара в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_category}}', 'movable_d', 'Показатель статуса возможности перемещения в виджете "вниз" категории товара в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_category}}', 'movable_l', 'Показатель статуса возможности перемещения в виджете "влево" категории товара в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_category}}', 'movable_r', 'Показатель статуса возможности перемещения в виджете "вправо" категории товара в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_category}}', 'removable', 'Показатель статуса возможности удаления категории товара в системе R-Keeper (0 - не удаляем, 1 - удаляем)');
        $this->addCommentOnColumn('{{%rk_category}}', 'removable_all', 'Показатель статуса возможности каскадного удаления связанных категорий товаров в системе R-Keeper (0 - не удаляемы, 1 - удаляемы)');
        $this->addCommentOnColumn('{{%rk_category}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_category}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_category}}', 'type', 'Тип элемента (1 - папка с элементами, NULL - корень)');
        $this->addCommentOnColumn('{{%rk_category}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_category}}', 'fid', 'Суррогатный ключ категории товара в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_category}}', 'version', 'Версия реализации категории товара в системе R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_category` comment "";');
        $this->dropCommentFromColumn('{{%rk_category}}', 'id');
        $this->dropCommentFromColumn('{{%rk_category}}', 'root');
        $this->dropCommentFromColumn('{{%rk_category}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_category}}', 'lft');
        $this->dropCommentFromColumn('{{%rk_category}}', 'rgt');
        $this->dropCommentFromColumn('{{%rk_category}}', 'lvl');
        $this->dropCommentFromColumn('{{%rk_category}}', 'prnt');
        $this->dropCommentFromColumn('{{%rk_category}}', 'name');
        $this->dropCommentFromColumn('{{%rk_category}}', 'icon');
        $this->dropCommentFromColumn('{{%rk_category}}', 'icon_type');
        $this->dropCommentFromColumn('{{%rk_category}}', 'active');
        $this->dropCommentFromColumn('{{%rk_category}}', 'selected');
        $this->dropCommentFromColumn('{{%rk_category}}', 'disabled');
        $this->dropCommentFromColumn('{{%rk_category}}', 'readonly');
        $this->dropCommentFromColumn('{{%rk_category}}', 'visible');
        $this->dropCommentFromColumn('{{%rk_category}}', 'collapsed');
        $this->dropCommentFromColumn('{{%rk_category}}', 'movable_u');
        $this->dropCommentFromColumn('{{%rk_category}}', 'movable_d');
        $this->dropCommentFromColumn('{{%rk_category}}', 'movable_l');
        $this->dropCommentFromColumn('{{%rk_category}}', 'movable_r');
        $this->dropCommentFromColumn('{{%rk_category}}', 'removable');
        $this->dropCommentFromColumn('{{%rk_category}}', 'removable_all');
        $this->dropCommentFromColumn('{{%rk_category}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_category}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_category}}', 'type');
        $this->dropCommentFromColumn('{{%rk_category}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_category}}', 'fid');
        $this->dropCommentFromColumn('{{%rk_category}}', 'version');
    }
}
