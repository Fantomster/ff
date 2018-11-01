<?php

use yii\db\Migration;

/**
 * Class m181101_140714_add_translations_to_waybill_create_genenaki_automaki
 */
class m181101_140714_add_translations_to_waybill_create_genenaki_automaki extends Migration
{
    public $translations = [
        'waybill.no_content_for_create_waybill' => 'По данному заказу нечего сопоставлять'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
