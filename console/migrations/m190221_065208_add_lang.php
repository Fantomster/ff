<?php

use yii\db\Migration;

/**
 * Class m190221_065208_add_lang
 */
class m190221_065208_add_lang extends Migration
{
    public $translations = [
        'orders.min_order_price_not_success' => 'Минимальная сумма одного из заказов не достигнута. Заказ %s необходимо еще %s %s',
        'order.min_order_price_not_success' => 'Минимальная сумма заказа не достигнута. Необходимо еще %s %s',
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
