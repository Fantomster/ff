<?php

use yii\db\Migration;

class m181115_160344_add_comments_table_edi_roaming_map extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `edi_roaming_map` comment "Таблица сведений о связях EDI поставщиков и ресторанов";');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'sender_edi_organization_id','Идентификатор организации-ресторана-отправителя в таблице edi_organization');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'recipient_edi_organization_id','Идентификатор организации-поставщика-получателя в таблице edi_organization');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'created_by_id','Идентификатор пользователя-создателя записи');
    }

    public function safeDown()
    {
        $this->execute('alter table `edi_roaming_map` comment "";');
        $this->dropCommentFromColumn('{{%edi_roaming_map}}', 'id');
        $this->dropCommentFromColumn('{{%edi_roaming_map}}', 'sender_edi_organization_id');
        $this->dropCommentFromColumn('{{%edi_roaming_map}}', 'recipient_edi_organization_id');
        $this->dropCommentFromColumn('{{%edi_roaming_map}}', 'created_by_id');
    }
}