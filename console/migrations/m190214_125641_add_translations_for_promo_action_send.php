<?php

use yii\db\Migration;

/**
 * Class m190214_125641_add_translations_for_promo_action_send
 */
class m190214_125641_add_translations_for_promo_action_send extends Migration
{
    public $translations = [
        'promo_action_not_found'    => 'Промо-акция не существует',
        'promo_action_code_error' => 'Указан не правильный промо-код',
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
