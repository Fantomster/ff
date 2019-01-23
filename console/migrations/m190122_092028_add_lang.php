<?php

use yii\db\Migration;

/**
 * Class m190122_092028_add_lang
 */
class m190122_092028_add_lang extends Migration
{
    public $translations = [
        'rkeeper.waybill.outer_number_additional.is_not_int' => 'Номер счета-фактуры должен быть целым числом.',
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
