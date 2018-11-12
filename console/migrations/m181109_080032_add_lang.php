<?php

use yii\db\Migration;

/**
 * Class m181109_080032_add_lang
 */
class m181109_080032_add_lang extends Migration
{
    public $translations = [
        'dictionary.category_not_found'           => 'Категория не найдена',
        'dictionary.directory_cannot_be_selected' => 'Папка не может быть выбрана для загрузки',
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
