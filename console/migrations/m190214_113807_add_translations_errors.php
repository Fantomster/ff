<?php

use yii\db\Migration;

class m190214_113807_add_translations_errors extends Migration
{
    public $translations = [
        'requested.page.does.not.exist' => 'Запрашиваемая страница не существует.'
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'error', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'error', $this->translations);
    }
}
