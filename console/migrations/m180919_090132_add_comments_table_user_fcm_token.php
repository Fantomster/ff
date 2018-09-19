<?php

use yii\db\Migration;

class m180919_090132_add_comments_table_user_fcm_token extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `user_fcm_token` comment "Таблица сведений о токенах пользователей в Firebase Cloud";');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'user_id','Идентификатор пользователя');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'token','Токен');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'device_id','Идентификатор электронного устройства');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%user_fcm_token}}', 'updated_at','Дата и время последнего изменения записи в таблице');
    }

    public function safeDown()
    {
        $this->execute('alter table `user_fcm_token` comment "";');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'id');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'user_id');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'token');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'device_id');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'created_at');
        $this->dropCommentFromColumn('{{%user_fcm_token}}', 'updated_at');
    }
}
