<?php

use yii\db\Migration;

/**
 * Class m181021_082932_add_translations_to_statuses_edi
 */
class m181021_082932_add_translations_to_statuses_edi extends Migration
{
    public $translations_ru = [
        'order.available_for_edi_order' => 'Доступно только для документов ЭДО',
        'order.status_must_be' => 'Должен быть статус ',
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
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations_ru);
    }
}
