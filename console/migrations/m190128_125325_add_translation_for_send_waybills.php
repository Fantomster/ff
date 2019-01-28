<?php

use yii\db\Migration;

/**
 * Class m190128_125325_add_translation_for_send_waybills
 */
class m190128_125325_add_translation_for_send_waybills extends Migration
{
    public $translations = [
        'integration.waybill_send'     => 'Накладная выгружена, № = ',
        'integration.waybill_not_send' => 'Накладная не выгружена, № = ',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }

}
