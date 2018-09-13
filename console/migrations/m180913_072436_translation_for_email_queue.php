<?php

use yii\db\Migration;

/**
 * Class m180913_072436_translation_for_email_queue
 */
class m180913_072436_translation_for_email_queue extends Migration
{
    public $translations = [
        'common.models.email_queue.status_new' => 'Ожидает отправки',
        'common.models.email_queue.status_sending' => 'Отправляется',
        'common.models.email_queue.status_confirmed' => 'Отправлено',
        'common.models.email_queue.status_failed' => 'Отправить не удалось',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
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
