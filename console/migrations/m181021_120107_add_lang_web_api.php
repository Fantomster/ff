<?php

use yii\db\Migration;

/**
 * Class m181021_120107_add_lang_web_api
 */
class m181021_120107_add_lang_web_api extends Migration
{
    public $translations_ru = [
        'document.not_support_type' => 'Формат документа не поддерживатеся',
        'document.waybill_in_the_state_of_reset_or_unloaded' => 'Накладная должна быть в статусе "Сброшена" или "Не выгружена"',
        'waybill_not_found' => 'Накладная не найдена',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
