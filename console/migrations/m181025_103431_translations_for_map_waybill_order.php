<?php

use yii\db\Migration;

/**
 * Class m181025_103431_translations_for_map_waybill_order
 */
class m181025_103431_translations_for_map_waybill_order extends Migration
{
    public $translations = [
        'document.replaced_order_not_found' => 'Заменяемый документ не найден или не является заказом',
        'document.document_not_found' => 'Документ не найден или не является документом от поставщика',
        'document.document_cancelled' => 'Документ в состоянии "Отменен"',
        'document.document_replaced_order_id_is_not_null' => 'Документ уже заменен',
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
