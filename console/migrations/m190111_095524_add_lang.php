<?php

use yii\db\Migration;

/**
 * Class m190111_095524_add_lang
 */
class m190111_095524_add_lang extends Migration
{
    public $translations = [
        'poster.not_set_app_id' => 'Необходимо заполнить Poster Application Id',
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
