<?php

use yii\db\Migration;

/**
 * Class m181122_083712_add_lang
 */
class m181122_083712_add_lang extends Migration
{
    public $translations = [
        'order.edit_access_denied'              => 'У вас нет прав на изменение заказа.',
        'order.edit_comment_access_denied'      => 'У вас нет прав на изменение комментария заказа.',
        'order.view_access_denied'              => 'У вас нет прав на просмотр заказа.',
        'order.already_cancel'                  => 'Заказ уже отменен',
        'order.already_done'                    => 'Заказ уже завершен',
        'order.add_product_empty'               => 'Не удалось распознать продукт, который вы хотите добавить',
        'order.delete_product_empty'            => 'Не удалось распознать продукт, который вы хотите удалить',
        'order.edit_product_empty'              => 'Не удалось распознать продукт, который вы хотите отредактировать',
        'order.add_product_is_already_in_order' => 'Этот продукт уже добавлен в заказ: %s',
        'order.bad_vendor'                      => 'В этот заказ можно добавлять товары только от поставщика: %s',
        'order.cancel_already_done'             => 'Нельзя отменить завершенный заказ',
        'order_content.empty'                   => 'В заказе нет позиций',
        'order_content.not_found'               => 'В заказе нет такой позиции',
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
