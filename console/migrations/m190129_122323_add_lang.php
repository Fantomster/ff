<?php

use yii\db\Migration;

class m190129_122323_add_lang extends Migration
{
    public $translations = [
        'api.rkws.components.rabbit.journal.not.save' => 'Сохранить изменения в журнале Rabbit не удалось.',
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
