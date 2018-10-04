<?php

use yii\db\Migration;

/**
 * Class m181002_094805_translations_for_waybill_const_statuses
 */
class m181002_094805_translations_for_waybill_const_statuses extends Migration
{
    public $translations = [
        'waybill.compared' => 'Сопоставлена',
        'waybill.formed' => 'Сформирована',
        'waybill.error' => 'Ошибка',
        'waybill.reset' => 'Сброшена',
        'waybill.unloaded' => 'Выгружена',
        'waybill.unloading' => 'Выгружается',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'web_api', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'web_api', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180913_072436_translation_for_email_queue cannot be reverted.\n";

        return false;
    }
    */
}
