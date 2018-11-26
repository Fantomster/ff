<?php

use yii\db\Migration;

/**
 * Class m181120_082849_add_lang
 */
class m181120_082849_add_lang extends Migration
{
    public $translations = [
        'service_iiko.empty_waybill_content' => 'В накладной нет позиций для выгрузки.'
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
