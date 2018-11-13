<?php

use yii\db\Migration;

/**
 * Class m181015_145045_add_translations_for_edi
 */
class m181015_145045_add_translations_for_edi extends Migration
{
    public $translations_ru = [
        'common.models.order_status.status_edo_sent_by_vendor' => 'Отправлен поставщиком',
        'common.models.order_status.status_edo_acceptance_finished' => 'Приемка завершена',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations_ru);
    }
}
