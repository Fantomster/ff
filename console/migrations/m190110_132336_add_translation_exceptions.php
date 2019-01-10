<?php

use yii\db\Migration;

/**
 * Class m190110_132336_add_translation_exceptions
 */
class m190110_132336_add_translation_exceptions extends Migration
{
    public $translations = [
        'Relocation prohibited by regionalization rules' => 'Пересещение запрещено правилами регионализации из-за заболевания - %s ',
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
