<?php

use yii\db\Migration;

/**
 * Class m181220_072516_add_lang_mail_order_changed
 */
class m181220_072516_add_lang_mail_order_changed extends Migration
{
    public $translations = [
        'mail.chat.order_changed.name' => 'Товар',
        'mail.chat.order_changed.article' => 'Артикул',
        'mail.chat.order_changed.quantity' => 'Кол-во',
        'mail.chat.order_changed.price' => 'Цена',
        'mail.chat.order_changed.sum' => 'Сумма',
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
}
