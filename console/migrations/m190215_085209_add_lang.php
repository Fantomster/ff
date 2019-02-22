<?php

use yii\db\Migration;

/**
 * Class m190215_085209_add_lang
 */
class m190215_085209_add_lang extends Migration
{
    public $translations = [
        'analog_web_api.sort.product_name' => 'Наименованию А-Я',
        'analog_web_api.sort._product_name' => 'Наименованию Я-А',
        'analog_web_api.sort.vendor_name' => 'Поставщику',
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
