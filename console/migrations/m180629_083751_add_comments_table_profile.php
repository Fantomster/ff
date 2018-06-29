<?php

use yii\db\Migration;

class m180629_083751_add_comments_table_profile extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `profile` comment "Таблица сведений о профилях пользователей системы";');
        $this->addCommentOnColumn('{{%profile}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%profile}}', 'user_id', 'Идентификатор пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%profile}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%profile}}', 'full_name', 'Полное имя пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'timezone', 'Временная зона, в которой работает пользователь');
        $this->addCommentOnColumn('{{%profile}}', 'phone', 'Номер телефона пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'avatar', 'Название файла, содержащего аватарку');
        $this->addCommentOnColumn('{{%profile}}', 'sms_allow', 'Показатель состояния согласия на смс-оповещения (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%profile}}', 'job_id', 'Идентификатор должности пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'gender', 'Идентификатор гендерного пола пользователя');
        $this->addCommentOnColumn('{{%profile}}', 'email', 'E-mail пользователя (копируется из таблицы user)');
        $this->addCommentOnColumn('{{%profile}}', 'email_allow', 'Показатель состояния согласия на Email-оповещения (0 - не согласен, 1 - согласен)');
    }

    public function safeDown()
    {
        $this->execute('alter table `profile` comment "";');
        $this->dropCommentFromColumn('{{%profile}}', 'id');
        $this->dropCommentFromColumn('{{%profile}}', 'user_id');
        $this->dropCommentFromColumn('{{%profile}}', 'created_at');
        $this->dropCommentFromColumn('{{%profile}}', 'updated_at');
        $this->dropCommentFromColumn('{{%profile}}', 'full_name');
        $this->dropCommentFromColumn('{{%profile}}', 'timezone');
        $this->dropCommentFromColumn('{{%profile}}', 'phone');
        $this->dropCommentFromColumn('{{%profile}}', 'avatar');
        $this->dropCommentFromColumn('{{%profile}}', 'sms_allow');
        $this->dropCommentFromColumn('{{%profile}}', 'job_id');
        $this->dropCommentFromColumn('{{%profile}}', 'gender');
        $this->dropCommentFromColumn('{{%profile}}', 'email');
        $this->dropCommentFromColumn('{{%profile}}', 'email_allow');
    }
}
