<?php

use yii\db\Migration;

class m181115_154924_add_comments_table_franchisee extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `franchisee` comment "Таблица сведений о франчайзи";');
        $this->addCommentOnColumn('{{%franchisee}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%franchisee}}', 'signed','Подписант');
        $this->addCommentOnColumn('{{%franchisee}}', 'legal_entity','Юридическое название организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'legal_address','Юридический адрес организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'legal_email','Официальный электронный ящик');
        $this->addCommentOnColumn('{{%franchisee}}', 'inn','ИНН организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'kpp','КПП организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'ogrn','ОГРН организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'bank_name','Наименование банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%franchisee}}', 'bik','БИК банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%franchisee}}', 'phone','Телефон организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'correspondent_account','Корреспондентский счёт банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%franchisee}}', 'checking_account','Расчётный счёт организации в банке');
        $this->addCommentOnColumn('{{%franchisee}}', 'info','Поле для заметок об организации');
        $this->addCommentOnColumn('{{%franchisee}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%franchisee}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%franchisee}}', 'type_id','Идентификатор типа франчайзи');
        $this->addCommentOnColumn('{{%franchisee}}', 'deleted','Показатель статуса удаления франчайзи (0 - не удалён, 1 - удалён)');
        $this->addCommentOnColumn('{{%franchisee}}', 'fio_manager','ФИО менеджера');
        $this->addCommentOnColumn('{{%franchisee}}', 'phone_manager','Телефон менеджера');
        $this->addCommentOnColumn('{{%franchisee}}', 'picture_manager','Аватар менеджера');
        $this->addCommentOnColumn('{{%franchisee}}', 'additional_number_manager','Дополнительный телефон менеджера');
        $this->addCommentOnColumn('{{%franchisee}}', 'receiving_organization','Количество организаций, с которыми работает франчайзи');
        $this->addCommentOnColumn('{{%franchisee}}', 'is_public_web','Показатель статуса разрешения публикации на сайте mixcart.ru (0 - не публиковать, 1 - публиковать)');
    }

    public function safeDown()
    {
        $this->execute('alter table `franchisee` comment "";');
        $this->dropCommentFromColumn('{{%franchisee}}', 'id');
        $this->dropCommentFromColumn('{{%franchisee}}', 'signed');
        $this->dropCommentFromColumn('{{%franchisee}}', 'legal_entity');
        $this->dropCommentFromColumn('{{%franchisee}}', 'legal_address');
        $this->dropCommentFromColumn('{{%franchisee}}', 'legal_email');
        $this->dropCommentFromColumn('{{%franchisee}}', 'inn');
        $this->dropCommentFromColumn('{{%franchisee}}', 'kpp');
        $this->dropCommentFromColumn('{{%franchisee}}', 'ogrn');
        $this->dropCommentFromColumn('{{%franchisee}}', 'bank_name');
        $this->dropCommentFromColumn('{{%franchisee}}', 'bik');
        $this->dropCommentFromColumn('{{%franchisee}}', 'phone');
        $this->dropCommentFromColumn('{{%franchisee}}', 'correspondent_account');
        $this->dropCommentFromColumn('{{%franchisee}}', 'checking_account');
        $this->dropCommentFromColumn('{{%franchisee}}', 'info');
        $this->dropCommentFromColumn('{{%franchisee}}', 'created_at');
        $this->dropCommentFromColumn('{{%franchisee}}', 'updated_at');
        $this->dropCommentFromColumn('{{%franchisee}}', 'type_id');
        $this->dropCommentFromColumn('{{%franchisee}}', 'deleted');
        $this->dropCommentFromColumn('{{%franchisee}}', 'fio_manager');
        $this->dropCommentFromColumn('{{%franchisee}}', 'phone_manager');
        $this->dropCommentFromColumn('{{%franchisee}}', 'picture_manager');
        $this->dropCommentFromColumn('{{%franchisee}}', 'additional_number_manager');
        $this->dropCommentFromColumn('{{%franchisee}}', 'receiving_organization');
        $this->dropCommentFromColumn('{{%franchisee}}', 'is_public_web');
    }
}