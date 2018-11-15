<?php

use yii\db\Migration;

/**
 * Class m181115_125508_add_translation_button
 */
class m181115_125508_add_translation_button extends Migration
{
    public $translations = [
        'frontend.views.layouts.client.integration.check_all' => 'Выделить все'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'message', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'message', $this->translations);
    }
}
