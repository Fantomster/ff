<?php

use yii\db\Migration;

/**
 * Class m190220_085545_add_lang
 */
class m190220_085545_add_lang extends Migration
{
    public $translations = [
        'dictionaries_were_already_loaded' => 'Словари уже были загружены!',
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

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190220_085545_add_lang cannot be reverted.\n";

        return false;
    }
    */
}
