<?php

use yii\db\Migration;

/**
 * Class m181228_095018_add_lang
 */
class m181228_095018_add_lang extends Migration
{
    public $translations_ru = [
        'vendor.is_edi' => 'Поставщик является EDI'
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
