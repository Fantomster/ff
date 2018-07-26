<?php

use yii\db\Migration;

/**
 * Class m180726_071217_api_web_traslations
 */
class m180726_071217_api_web_traslations extends Migration
{
    public $translations_ru = [
        'bad_old_password' => 'Действующий пароль введен с ошибкой.',
        'bad_password' => 'Это плохой пароль, придумайте другой, например: %s'
    ];

    public $translations_en = [
        'bad_old_password' => 'The current password entered incorrectly.',
        'bad_password' => 'It is a bad password, create another, for example: %s'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::insertCategory('en', 'api_web', $this->translations_en);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::deleteCategory('en', 'api_web', $this->translations_en);
    }
}
