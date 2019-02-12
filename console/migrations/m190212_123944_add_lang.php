<?php

use yii\db\Migration;

/**
 * Class m190212_123944_add_lang
 */
class m190212_123944_add_lang extends Migration
{
    public $translations = [
        'lazy_vendor.not_found'        => 'Поставщик не найден',
        'lazy_vendor.not_is_my_vendor' => 'Вы не работаете с этим поставщиком',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', [
            'organization_contact.test_message' => 'MixCart шлёт привет!'
        ]);
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', [
            'organization_contact.test_message' => 'MixCart шлёт привет!'
        ]);
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
