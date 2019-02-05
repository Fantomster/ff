<?php

use yii\db\Migration;

/**
 * Class m190205_122700_add_lang
 */
class m190205_122700_add_lang extends Migration
{
    public $translations = [
        'preorder.no_vendor_product_in_cart' => 'В корзине нет товаров данного поставщика.',
        'preorder.cart_was_not_found' => 'Вы ещё не создавали корзину.',
        'preorder.vendor_id_not_found' => 'У вас нет поставщика с таким id.',
        'preorder.cart_empty' => 'Корзина в данный момент пуста.',
        'preorder.not_your_vendor' => 'Вы не работаете с данным поставщиком.',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
