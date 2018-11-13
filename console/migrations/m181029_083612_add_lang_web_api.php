<?php

use yii\db\Migration;

/**
 * Class m181029_083612_add_lang_web_api
 */
class m181029_083612_add_lang_web_api extends Migration
{
    public $translations = [
        'waybill.outer_product_not_found' => 'Продукт у.с. не найден',
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
