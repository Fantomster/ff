<?php

use yii\db\Migration;

/**
 * Class m190129_085016_add_translation_for_vendor_search
 */
class m190129_085016_add_translation_for_vendor_search extends Migration
{
    public $translations = [
        'email_belong_restaurant' => 'Данный email принадлежит ресторану',
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
