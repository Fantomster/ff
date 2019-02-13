<?php

use yii\db\Migration;

/**
 * Class m190211_091841_add_lang
 */
class m190211_091841_add_lang extends Migration
{
    public $translations = [
        'preorder.wrong_value_type' => 'Неправильное значение параметра.',
        'preorder.product_already_exist_in_preorder' => 'В данном предзаказе уже есть данный товар.',
        'preorder.not_your_supplier' => 'Вы не работаете с данным поставщиком.',
        'preorder.not_your_catalog' => 'Этот каталог не принадлежит вам.',
        'preorder.product_id_repeat' => 'Id товаров не должны повторяться.',
        'preorder.product_not_found' => 'Один из товаров не найден.',
        'preorder.product_not_in_cat' => 'В данном каталоге нет данного товара.',
        'preorder.not_supp_product' => 'У данного поставщика нет такого товара.',
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
