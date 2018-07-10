<?php

use yii\db\Migration;

class m180709_092832_add_comments_table_rk_storetree extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `rk_storetree` comment "Таблица сведений о дереве складов в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'root', 'Идентификатор корневого элемента справочника складов в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'rid', 'Идентификатор склада в системе R-Keeper ');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'lft', 'Указатель на "левый" элемент в справочнике складов в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'rgt', 'Указатель на "правый" элемент в справочнике складов в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'lvl', 'Показатель поколения элемента в справочнике складов в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'prnt', 'Указатель на родительский элемент в справочнике складов в системе R-Keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'name', 'Наименование склада в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'icon', 'Ссылка на иконку склада в системе R-Keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'icon_type', 'Тип файла иконки склада в системе R-Keeper (не используется)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'active', 'Показатель статуса активности склада в системе R-Keeper (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'selected', 'Показатель статуса отмеченности склада в системе R-Keeper (0 - не отмечен, 1 - активен)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'disabled', 'Показатель статуса выключенности склада в системе R-Keeper (0 - не выключен, 1 - выключен)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'readonly', 'Показатель статуса возможности изменения склада в системе R-Keeper (0 - не изменяется, 1 - изменяется)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'visible', 'Показатель статуса видимости склада в системе R-Keeper (0 - не видим, 1 - видим)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'collapsed', 'Показатель статуса свёрнутости склада в системе R-Keeper (0 - не свёрнут, 1 - свёрнут)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'movable_u', 'Показатель статуса возможности перемещения в виджете "вверх" склада в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'movable_d', 'Показатель статуса возможности перемещения в виджете "вниз" склада в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'movable_l', 'Показатель статуса возможности перемещения в виджете "влево" склада в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'movable_r', 'Показатель статуса возможности перемещения в виджете "вправо" склада в системе R-Keeper (0 - не перемещаем, 1 - перемещаем)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'removable', 'Показатель статуса возможности удаления склада в системе R-Keeper (0 - не удаляем, 1 - удаляем)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'removable_all', 'Показатель статуса возможности каскадного удаления связанных складов в системе R-Keeper (0 - не удаляемы, 1 - удаляемы)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'type', 'тип элемента (1 - простой элемент, 2 - папка с элементами)');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'acc', 'Идентификатор организации');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'fid', 'Суррогатный ключ склада в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_storetree}}', 'version', 'Версия реализации склада в системе R-Keeper');
    }

    public function safeDown()
    {
        $this->execute('alter table `rk_storetree` comment "";');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'id');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'root');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'rid');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'lft');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'rgt');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'lvl');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'prnt');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'name');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'icon');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'icon_type');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'active');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'selected');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'disabled');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'readonly');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'visible');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'collapsed');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'movable_u');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'movable_d');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'movable_l');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'movable_r');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'removable');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'removable_all');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'created_at');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'updated_at');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'type');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'acc');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'fid');
        $this->dropCommentFromColumn('{{%rk_storetree}}', 'version');
    }
}
