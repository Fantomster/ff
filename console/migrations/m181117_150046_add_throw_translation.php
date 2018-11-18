<?php

use yii\db\Migration;

/**
 * Class m181117_150046_add_throw_translation
 */
class m181117_150046_add_throw_translation extends Migration
{
    public $translations = [
        'choose_integration_service' => 'У вас не выбран сервис интеграции'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
