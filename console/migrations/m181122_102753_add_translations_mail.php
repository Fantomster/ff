<?php

use yii\db\Migration;

/**
 * Class m181122_102753_add_translations_mail
 */
class m181122_102753_add_translations_mail extends Migration
{
    public $translations = [
        'common.mail.accept_restaurant_invite.new_account' => 'Ваша учетная запись',
        'common.mail.accept_restaurant_invite.username'    => 'Логин (email)',
        'common.mail.accept_restaurant_invite.password'    => 'Пароль',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
