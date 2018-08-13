<?php

use yii\db\Migration;

/**
 * Class m180813_083542_api_web_translation
 */
class m180813_083542_api_web_translation extends Migration
{
    public $translations_ru = [
        'api_web.catalog.key.product' => 'Продукт',
        'api_web.catalog.key.article' => 'Артикул',
        'api_web.catalog.key.other' => 'Другое',
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
