<?php

use yii\db\Migration;

/**
 * Class m180926_120700_translations_for_new_letters
 */
class m180926_120700_translations_for_new_letters extends Migration
{
    public $translations = [
        'common.mail.order_created.string1' => 'Перейти к заказу',
        'common.models.order_status.status_awaiting_accept_from_vendor' => 'Ожидает потверждения поставщика',
        'common.mail.order.delivery_date' => 'Дата доставки',
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
        echo "m180926_120700_translations_for_new_letters cannot be reverted.\n";

        return false;
    }
    */
}
