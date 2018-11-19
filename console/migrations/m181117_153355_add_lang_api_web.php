<?php

use yii\db\Migration;

/**
 * Class m181117_153355_add_lang_api_web
 */
class m181117_153355_add_lang_api_web extends Migration
{
    public $translations = [
        'store.is_category' => 'Категория не может быть выбрана в качестве склада.',
        'store.not_found'   => 'Склад не найден',
        'agent.not_found'   => 'Агент не найден',
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
