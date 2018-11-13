<?php

use yii\db\Migration;

/**
 * Class m181025_071006_add_lang_to_order_set_document_number
 */
class m181025_071006_add_lang_to_order_set_document_number extends Migration
{
    public $translations_ru = [
        'bad_service_id_in_order' => 'Дествие нельзя применять к заказу с service_id = %s. Доступно только для заказов с service_id = %s'
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
