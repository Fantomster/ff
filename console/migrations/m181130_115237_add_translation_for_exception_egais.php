<?php

use yii\db\Migration;

/**
 * Class m181130_115237_add_translation_for_exception_egais
 */
class m181130_115237_add_translation_for_exception_egais extends Migration
{
    public $translations = [
        'dictionary.request_error' => 'Недопустимый запрос',
        'dictionary.egais_get_setting_error' => 'Отсутствуют настройки ЕГАИС',
        'dictionary.egais_set_setting_error' => 'Ошибка, при сохранении настроек',
        'dictionary.organization_not_found' => 'Организация не найдена',
        'dictionary.egais_type_document_error' => 'Неизвестный тип документа',
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
        echo "m181130_115237_add_translation_for_exception_egais cannot be reverted.\n";

        return false;
    }
    */
}
