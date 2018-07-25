<?php

use yii\db\Migration;

/**
 * Class m180717_070513_add_translations_for_merc_pdf
 */
class m180717_070513_add_translations_for_merc_pdf extends Migration
{
    public $translations = [
        'frontend.client.integration.pdf' => 'Загрузка PDF',
        'frontend.client.integration.pdf_prepare' => 'Формируем PDF...',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'message', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'message', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180717_070513_add_translations_for_merc_pdf cannot be reverted.\n";

        return false;
    }
    */
}
