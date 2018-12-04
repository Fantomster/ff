<?php

use yii\db\Migration;

/**
 * Class m181126_141903_add_translation_for_short_pdf_vsd
 */
class m181126_141903_add_translation_for_short_pdf_vsd extends Migration
{
    public $translations = [
        'frontend.client.integration.short_pdf' => 'Загрузка сжатого PDF',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'message', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'message', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181126_141903_add_translation_for_short_pdf_vsd cannot be reverted.\n";

        return false;
    }
    */
}
