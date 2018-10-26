<?php

use yii\db\Migration;

/**
 * Class m181023_063729_add_lang_web_api_waybill
 */
class m181023_063729_add_lang_web_api_waybill extends Migration
{
    public $translations_ru = [
        'waybill.content_exists' => 'Позиция в накладной уже существует.',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
