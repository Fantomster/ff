<?php

use yii\db\Migration;

/**
 * Class m190217_102109_add_lang
 */
class m190217_102109_add_lang extends Migration
{
    public $translations = [
        'orders.requested_delivery_empty' => 'В некоторых заказах не проставлена дата доставки',
        'order.requested_delivery_empty'  => 'В заказе не проставлена дата доставки',
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
