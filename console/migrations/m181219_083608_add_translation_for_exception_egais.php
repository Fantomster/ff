<?php

use yii\db\Migration;

/**
 * Class m181219_083608_add_translation_for_exception_egais
 */
class m181219_083608_add_translation_for_exception_egais extends Migration
{
    public $translations = [
        'dictionary.connection_error_egais' => 'Не удалось подключиться, проверьте настройки ЕГАИС',
        'dictionary.parse_error_egais' => 'Ошибка парсинга',
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
        echo "m181219_083608_add_translation_for_exception_egais cannot be reverted.\n";

        return false;
    }
    */
}
