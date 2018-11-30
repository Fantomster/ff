<?php

use yii\db\Migration;

class m181130_120511_add_comments_table_edi_organization extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_organization` comment "Таблица сведений о настройках аккаунтов организаций в системе EDI";');
        $this->addCommentOnColumn('{{%edi_organization}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_organization}}', 'organization_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%edi_organization}}', 'gln_code','Глобальный идентификатор организации в системе EDI');
        $this->addCommentOnColumn('{{%edi_organization}}', 'login','Логин для доступа в личный кабинет организации у EDI провайдера');
        $this->addCommentOnColumn('{{%edi_organization}}', 'pass','Пароль для доступа в личный кабинет организации у EDI провайдера');
        $this->addCommentOnColumn('{{%edi_organization}}', 'int_user_id','Идентификатор пользователя в системе Leradata (если используется Leradata)');
        $this->addCommentOnColumn('{{%edi_organization}}', 'token','Токен пользователя в системе Leradata (если используется Leradata)');
        $this->addCommentOnColumn('{{%edi_organization}}', 'provider_id','Идентификатор EDI провайдера');
        $this->addCommentOnColumn('{{%edi_organization}}', 'provider_priority','Приоритет проовайдера (чем меньше значение, тем выше приоритет)');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_organization` comment "";');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'id');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'organization_id');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'gln_code');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'login');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'pass');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'int_user_id');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'token');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'provider_id');
        $this->dropCommentFromColumn('{{%edi_organization}}', 'provider_priority');
    }
}
