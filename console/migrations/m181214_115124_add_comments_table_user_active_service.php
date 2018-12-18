<?php

use yii\db\Migration;

class m181214_115124_add_comments_table_user_active_service extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `user_active_service` comment "Таблица сведений о последнем учётном сервисе, которым воспользовались сотрудники организаций";');
        $this->addCommentOnColumn('{{%user_active_service}}', 'user_id', 'Идентификатор пользователя');
        $this->addCommentOnColumn('{{%user_active_service}}', 'organization_id','Идентификатор организации, сотрудником которой является пользователь');
        $this->addCommentOnColumn('{{%user_active_service}}', 'service_id','Идентификатор учётного сервиса');
    }

    public function safeDown()
    {
        $this->execute('alter table `user_active_service` comment "";');
        $this->dropCommentFromColumn('{{%user_active_service}}', 'user_id');
        $this->dropCommentFromColumn('{{%user_active_service}}', 'organization_id');
        $this->dropCommentFromColumn('{{%user_active_service}}', 'service_id');
    }
}
