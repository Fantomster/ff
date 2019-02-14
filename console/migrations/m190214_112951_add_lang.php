<?php

use yii\db\Migration;

/**
 * Class m190214_112951_add_lang
 */
class m190214_112951_add_lang extends Migration
{
    public $translations = [
        'dictionary.egais_get_product' => 'Товар не найден!'
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
