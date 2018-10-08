<?php

use yii\db\Migration;

class m180919_085913_add_comments_table_user_token extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `user_token` comment "Таблица сведений о токенах пользователей при авторизации";');
        $this->addCommentOnColumn('{{%user_token}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%user_token}}', 'user_id','Идентификатор пользователя');
        $this->addCommentOnColumn('{{%user_token}}', 'type','Тип операции при получении токена (1 - активация е-мэйла, 3 - смена пароля)');
        $this->addCommentOnColumn('{{%user_token}}', 'token','Токен');
        $this->addCommentOnColumn('{{%user_token}}', 'data','Результат применения токена вместе с датой и временем');
        $this->addCommentOnColumn('{{%user_token}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%user_token}}', 'expired_at','Дата и время окончания действия токена');
        $this->addCommentOnColumn('{{%user_token}}', 'pin','Пин-код для использования токена');
    }

    public function safeDown()
    {
        $this->execute('alter table `user_token` comment "";');
        $this->dropCommentFromColumn('{{%user_token}}', 'id');
        $this->dropCommentFromColumn('{{%user_token}}', 'user_id');
        $this->dropCommentFromColumn('{{%user_token}}', 'type');
        $this->dropCommentFromColumn('{{%user_token}}', 'token');
        $this->dropCommentFromColumn('{{%user_token}}', 'data');
        $this->dropCommentFromColumn('{{%user_token}}', 'created_at');
        $this->dropCommentFromColumn('{{%user_token}}', 'expired_at');
        $this->dropCommentFromColumn('{{%user_token}}', 'pin');
    }
}
