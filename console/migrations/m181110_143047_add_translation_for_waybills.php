<?php

use yii\db\Migration;

/**
 * Class m181110_143047_add_translation_for_waybills
 */
class m181110_143047_add_translation_for_waybills extends Migration
{
    public $translations = [
        'waybill.you_dont_have_order_content' => 'Выбранный Заказ/Документ не содержит позиций (блин, как это получилось то?)',
        'waybill.you_dont_have_licenses_for_services' => 'У вашей организации нет активных лицензий интеграции',
        'waybill.you_dont_have_order_content_for_waybills' => 'Выбранный Заказ/Документ не содержит позиций нераспределенных по накладным',
        'waybill.you_dont_have_mapped_products' => 'Выбранный Заказ/Документ содержит позиции, для которых не задано сопоставление',
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
