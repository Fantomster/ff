<?php

use yii\db\Migration;

/**
 * Class m190212_112420_add_lang_for_rbac
 */
class m190212_112420_add_lang_for_rbac extends Migration
{
    public $translations = [
        'You are not allowed to perform this action.' => 'Вам не разрешено выполнять это действие.'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'yii', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'yii', $this->translations);
    }
}
