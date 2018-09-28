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
        'common.mail.order_created.vendor_new' => 'Вам поступил новый заказ. Пожалуйста, просмотрите заказ и скорректируйте его при необходимости.',
        'common.mail.order_created.client_new' => 'Вы создали новый заказ. Пожалуйста, просмотрите заказ и скорректируйте его при необходимости.',
        'common.mail.order_changed.self_title' => 'Вы изменили детали заказа №',
        'common.mail.order_changed.another_party_title' => '{org_name} изменил детали заказа №',
        'common.mail.order_changed.string1' => 'Измененный заказ вы можете просмотреть ниже.',
        'common.mail.order_changed.string2' => 'Информация об изменениях была отправлена поставщику по электронной почте и SMS.',
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
