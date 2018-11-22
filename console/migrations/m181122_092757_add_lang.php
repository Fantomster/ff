<?php

use yii\db\Migration;

/**
 * Class m181122_092757_add_lang
 */
class m181122_092757_add_lang extends Migration
{
    public $translations = [
        'auth_failed' => 'Не удалось авторизоваться. Повторите ввод логина и пароля.'
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
