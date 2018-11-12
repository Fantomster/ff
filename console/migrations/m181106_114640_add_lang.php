<?php

use yii\db\Migration;

/**
 * Class m181106_114640_add_lang
 */
class m181106_114640_add_lang extends Migration
{
    public $translations = [
        'waybill.content_not_found' => 'Содержимое накладной не найдено.'
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
