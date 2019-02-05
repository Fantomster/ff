<?php

use yii\db\Migration;

/**
 * Class m190205_074233_add_lang
 */
class m190205_074233_add_lang extends Migration
{
    public $translations = [
        'preorder.not_found' => 'Предзаказ не найден.',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
