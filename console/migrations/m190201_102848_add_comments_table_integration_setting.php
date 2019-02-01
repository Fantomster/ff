<?php

use yii\db\Migration;

class m190201_102848_add_comments_table_integration_setting extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `integration_setting` comment "Таблица сведений о настройках интеграций";');
        $this->addCommentOnColumn('{{%integration_setting}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting}}', 'name', 'Наименование настройки интеграций');
        $this->addCommentOnColumn('{{%integration_setting}}', 'default_value', 'Значение настройки интеграций по умолчанию');
        $this->addCommentOnColumn('{{%integration_setting}}', 'comment', 'Комментарий с подробным описанием настройки интеграций');
        $this->addCommentOnColumn('{{%integration_setting}}', 'type', 'Тип поля настройки интеграций');
        $this->addCommentOnColumn('{{%integration_setting}}', 'is_active', 'Показатель состояния активности настройки интеграций (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%integration_setting}}', 'item_list', 'Список значений настройки интеграций по умолчанию в формате JSON для отображения при начальном выборе');
        $this->addCommentOnColumn('{{%integration_setting}}', 'service_id', 'Идентификатор учётного сервиса, к которому относится данная настройка');
        $this->addCommentOnColumn('{{%integration_setting}}', 'required_moderation', 'Показатель необходимости модерации настройки интеграций (0 - не обязательно модерировать, 1 - обязательно модерировать)');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_setting` comment "";');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'id');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'name');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'default_value');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'comment');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'type');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'is_active');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'item_list');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'service_id');
        $this->dropCommentFromColumn('{{%integration_setting}}', 'required_moderation');
    }
}
