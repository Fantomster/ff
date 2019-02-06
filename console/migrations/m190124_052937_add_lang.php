<?php

use yii\db\Migration;

/**
 * Class m190124_052937_add_lang
 */
class m190124_052937_add_lang extends Migration
{
    public $translations = [
        'empty_service_response' => 'Получен пустой ответ от учетной системы.',
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
