<?php

use yii\db\Migration;

/**
 * Class m181023_142734_api_web_lang
 */
class m181023_142734_api_web_lang extends Migration
{
    public $translations_ru = [
        'service_iiko.already_success_unloading_waybill' => 'Накладная уже вгружена ранее.',
        'service_iiko.no_ready_unloading_waybill'        => 'Накладная не готова к выгрузке.',
        'service_iiko.success_unloading_waybill'         => 'Накладная успешно выгружена.'
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
