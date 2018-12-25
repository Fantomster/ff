<?php

use yii\db\Migration;

class m181225_122854_add_translate_for_firebase extends Migration
{
    public $translations_ru = [
        'frontend.controllers.settings.change.updated' => 'Настройки успешно обновлены!',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
