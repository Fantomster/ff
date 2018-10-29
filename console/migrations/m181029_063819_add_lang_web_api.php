<?php

use yii\db\Migration;

/**
 * Class m181029_063819_add_lang_web_api
 */
class m181029_063819_add_lang_web_api extends Migration
{
    public $translations = [
        'waybill.error_reset_positions' => 'Ошибка при сбросе позиций накладной.',
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
