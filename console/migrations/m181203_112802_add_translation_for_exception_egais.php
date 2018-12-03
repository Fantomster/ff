<?php

use yii\db\Migration;

/**
 * Class m181203_112802_add_translation_for_exception_egais
 */
class m181203_112802_add_translation_for_exception_egais extends Migration
{
    public $translations = [
        'dictionary.act_write_off_number_error' => 'Номер акта должен быть уникальным',
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
        echo "m181203_112802_add_translation_for_exception_egais cannot be reverted.\n";

        return false;
    }
    */
}
