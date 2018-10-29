<?php

use yii\db\Migration;

/**
 * Class m181022_102522_add_translation_order_content_not_for_this_waybill
 */
class m181022_102522_add_translation_order_content_not_for_this_waybill extends Migration
{
    public $translations_ru = [
        'waybill.order_content_not_for_this_waybill' => 'Нельзя добавить позицию к накладной из другого заказа',
        'waybill.order_content_allready_has_waybill_content' => 'Позиция заказа уже имеет позицию накладной'
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
