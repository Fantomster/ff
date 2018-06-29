<?php

use yii\db\Migration;

class m180629_080640_add_comments_table_user extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `user` comment "Таблица сведений о пользователях системы";');
        $this->addCommentOnColumn('{{%user}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%user}}', 'role_id', 'Идентификатор роли пользователя в системе');
        $this->addCommentOnColumn('{{%user}}', 'status', 'Показатель статуса (0 - не активен, 1 - активен, 2 - ожидает подтверждения регистрации)');
        $this->addCommentOnColumn('{{%user}}', 'email', 'Е-мэйл пользователя');
        $this->addCommentOnColumn('{{%user}}', 'username', 'Ник пользователя в системе');
        $this->addCommentOnColumn('{{%user}}', 'password', 'Хэш пароля пользователя');
        $this->addCommentOnColumn('{{%user}}', 'auth_key', 'Хэш авторизационного ключа пользователя');
        $this->addCommentOnColumn('{{%user}}', 'access_token', 'Токен доступа пользователя');
        $this->addCommentOnColumn('{{%user}}', 'logged_in_ip', 'ip-адрес, с которого в последний раз залогинился пользователь');
        $this->addCommentOnColumn('{{%user}}', 'logged_in_at', 'Дата и время последней авторизации пользователя в системе');
        $this->addCommentOnColumn('{{%user}}', 'first_logged_in_at', 'Дата и время первой авторизации пользователя в системе');
        $this->addCommentOnColumn('{{%user}}', 'created_ip', 'ip-адрес, с которого был создан аккаунт пользователя');
        $this->addCommentOnColumn('{{%user}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%user}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%user}}', 'banned_at', 'Дата и время блокировки аккаунта пользователя');
        $this->addCommentOnColumn('{{%user}}', 'banned_reason', 'Причина блокировки пользователя');
        $this->addCommentOnColumn('{{%user}}', 'organization_id', 'Идентификатор организации, которую представляет пользователь');
        $this->addCommentOnColumn('{{%user}}', 'type', 'Тип записи для тестов (не используется)');
        $this->addCommentOnColumn('{{%user}}', 'subscribe', 'Показатель состояния наличия согласия на подписку получения рекламных материалов');
        $this->addCommentOnColumn('{{%user}}', 'send_manager_message', 'Показатель состояния согласия на получение технических сообщений от менеджера (для маркетинга)');
        $this->addCommentOnColumn('{{%user}}', 'send_week_message', 'Показатель состояния согласия на получение еженедельных сообщений от менеджеров');
        $this->addCommentOnColumn('{{%user}}', 'send_demo_message', 'Показатель состояния согласия на получение демонстрационных сообщений от менеджеров');
        $this->addCommentOnColumn('{{%user}}', 'language', 'Двухбуквенное обозначение языка, на котором в системе работает пользователь');
    }

    public function safeDown()
    {
        $this->execute('alter table `user` comment "";');
        $this->dropCommentFromColumn('{{%user}}', 'id');
        $this->dropCommentFromColumn('{{%user}}', 'role_id');
        $this->dropCommentFromColumn('{{%user}}', 'status');
        $this->dropCommentFromColumn('{{%user}}', 'email');
        $this->dropCommentFromColumn('{{%user}}', 'username');
        $this->dropCommentFromColumn('{{%user}}', 'password');
        $this->dropCommentFromColumn('{{%user}}', 'auth_key');
        $this->dropCommentFromColumn('{{%user}}', 'access_token');
        $this->dropCommentFromColumn('{{%user}}', 'logged_in_ip');
        $this->dropCommentFromColumn('{{%user}}', 'logged_in_at');
        $this->dropCommentFromColumn('{{%user}}', 'first_logged_in_at');
        $this->dropCommentFromColumn('{{%user}}', 'created_ip');
        $this->dropCommentFromColumn('{{%user}}', 'created_at');
        $this->dropCommentFromColumn('{{%user}}', 'updated_at');
        $this->dropCommentFromColumn('{{%user}}', 'banned_at');
        $this->dropCommentFromColumn('{{%user}}', 'banned_reason');
        $this->dropCommentFromColumn('{{%user}}', 'organization_id');
        $this->dropCommentFromColumn('{{%user}}', 'type');
        $this->dropCommentFromColumn('{{%user}}', 'subscribe');
        $this->dropCommentFromColumn('{{%user}}', 'send_manager_message');
        $this->dropCommentFromColumn('{{%user}}', 'send_week_message');
        $this->dropCommentFromColumn('{{%user}}', 'send_demo_message');
        $this->dropCommentFromColumn('{{%user}}', 'language');
    }
}
