<?php

use yii\db\Migration;

/**
 * Class m181102_090758_add_lang
 */
class m181102_090758_add_lang extends Migration
{
    public $translations = [
        'waybill.no_store_for_create_waybill' => 'По данному заказу не настроены склады для создания накладных',
        'waybill.no_map_for_create_waybill'   => 'По данному заказу не настроено сопоставление для создания накладных'
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
