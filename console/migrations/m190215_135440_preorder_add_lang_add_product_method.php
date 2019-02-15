<?php

use yii\db\Migration;

/**
 * Class m190215_135440_preorder_add_lang_add_product_method
 */
class m190215_135440_preorder_add_lang_add_product_method extends Migration
{
    public $translations = [
        'preorder.product_is_in_preorder' => 'Один из товаров уже есть в предзаказе.',
        'preorder.cannot_add_product_to_some_order' => 'В один из заказов нельзя добавить товар.'
    ];

    /**
     * @return bool|void
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
