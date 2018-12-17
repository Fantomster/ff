<?php

use yii\db\Migration;

class m181214_114423_add_comments_table_amo_fields extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `amo_fields` comment "Таблица сведений о полях для amoCRM";');
        $this->addCommentOnColumn('{{%amo_fields}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%amo_fields}}', 'amo_field','Значение поля FIELDS из формы на лендинге');
        $this->addCommentOnColumn('{{%amo_fields}}', 'responsible_user_id','Идентификатор ответственного менеджера');
        $this->addCommentOnColumn('{{%amo_fields}}', 'pipeline_id','Идентификатор "воронки"');
    }

    public function safeDown()
    {
        $this->execute('alter table `amo_fields` comment "";');
        $this->dropCommentFromColumn('{{%amo_fields}}', 'id');
        $this->dropCommentFromColumn('{{%amo_fields}}', 'amo_field');
        $this->dropCommentFromColumn('{{%amo_fields}}', 'responsible_user_id');
        $this->dropCommentFromColumn('{{%amo_fields}}', 'pipeline_id');
    }
}
