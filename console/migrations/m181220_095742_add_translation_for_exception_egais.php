<?php

use yii\db\Migration;

/**
 * Class m181220_095742_add_translation_for_exception_egais
 */
class m181220_095742_add_translation_for_exception_egais extends Migration
{
    public $translations = [
        'dictionary.save_act_error_egais' => 'Не удалось сохранить акт',
        'dictionary.save_ticket_and_act_error_egais' => 'Не удалось сохранить тикет и акт',
        'dictionary.save_product_and_act_error_egais' => 'Не удалось сохранить продукт и акт',
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
