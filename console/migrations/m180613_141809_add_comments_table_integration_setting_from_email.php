<?php

use yii\db\Migration;

class m180613_141809_add_comments_table_integration_setting_from_email extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `integration_setting_from_email` comment "Таблица сведений о настройках почтовых серверов для получения накладных от поставщиков";');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'organization_id', 'Идентификатор организации - получателя накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'server_type', 'Тип почтового сервера для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'server_host', 'Почтовый сервер для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'server_port', 'Порт почтового сервера для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'server_ssl', 'Флажок использования SSL для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'user', 'Логин для входа на почтовый сервер для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'password', 'Пароль для входа на почтовый сервер для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'is_active', 'Флажок показателя активности данного почтового ящика для получения накладных');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_setting_from_email` comment "";');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'id');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'organization_id');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'server_type');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'server_host');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'server_port');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'server_ssl');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'user');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'password');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'is_active');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'created_at');
        $this->dropCommentFromColumn('{{%integration_setting_from_email}}', 'updated_at');
    }

}
