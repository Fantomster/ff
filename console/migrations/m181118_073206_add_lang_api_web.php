<?php

use yii\db\Migration;

/**
 * Class m181118_073206_add_lang_api_web
 */
class m181118_073206_add_lang_api_web extends Migration
{
    public $translations = [
        'order.change.content'                => 'Изменили детали заказа:',
        'order.delete.content'                => 'Удалили из заказа:',
        'order.access.change.denied'          => 'Нет дуступа к изменению заказу',
        'order.access.change.canceled_status' => 'Заказ в статусе \'Отменен\' нельзя редактировать.',
        'order.discount.types'                => 'Тип скидки должен быть FIXED или PERCENT',
        'order.discount.empty_amount'         => 'Вы не указали значение скидки',
        'order.discount.100_percent'          => 'Скидка не может быть больше 100%',
        'order.discount.big_amount'           => 'Скидка не может быть больше суммы заказа',
        'error.request'                       => 'Это плохой запрос, мы не будем его обрабатывать',
        'order.notice.total_price'            => 'Сумма заказа:',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', [
            'common.models.product_name' => 'Продукт'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', [
            'common.models.product_name' => 'Продукт'
        ]);
    }
}
