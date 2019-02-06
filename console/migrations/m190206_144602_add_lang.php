<?php

use yii\db\Migration;

/**
 * Class m190206_144602_add_lang
 */
class m190206_144602_add_lang extends Migration
{

    public $translations = [
        'preorder.wrong_preorder' => 'Неправильный предзаказ'
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }
}
