<?php

use yii\db\Migration;

class m181214_113729_add_comments_table_buisiness_info extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `buisiness_info` comment "Таблица дополнительных сведений об организациях";');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'organization_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'info','Поле для заметок об организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'updated_at','Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'signed','Показатель состояния подписи организации на франшизу (0 - не подписана, 1 - подписана)');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'legal_entity','Юридическое название организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'legal_address','Юридический адрес организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'legal_email','Официальный электронный ящик организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'inn','ИНН организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'kpp','КПП организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'ogrn','ОГРН организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'bank_name','Наименование банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'bik','БИК банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'correspondent_account','Корреспондентский счёт банка, в котором обслуживается организация');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'checking_account','Расчётный счёт организации в данном банке');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'phone','Телефон организации');
        $this->addCommentOnColumn('{{%buisiness_info}}', 'reward','Процент с оборота организации');
    }

    public function safeDown()
    {
        $this->execute('alter table `buisiness_info` comment "";');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'id');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'organization_id');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'info');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'created_at');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'updated_at');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'signed');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'legal_entity');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'legal_address');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'legal_email');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'inn');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'kpp');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'ogrn');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'bank_name');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'bik');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'correspondent_account');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'checking_account');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'phone');
        $this->dropCommentFromColumn('{{%buisiness_info}}', 'reward');
    }
}
