<?php

use yii\db\Migration;

class m190125_090026_add_lang extends Migration
{
    public $translations = [
        'backend.controllers.vats.not.save' => 'Сохранить ставки налогов не удалось',
        'backend.controllers.vats.not.correct' => 'Перечень ставок налогов введён некорректно!',
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
