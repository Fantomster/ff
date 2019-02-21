<?php

use yii\db\Migration;

/**
 * Class m190220_080252_lazy_vendor_add_product_to_catalog_add_lang
 */
class m190220_080252_lazy_vendor_add_product_to_catalog_add_lang extends Migration
{
    public $translations = [
        'catalog.product_exist' => 'В каталоге уже есть такой продукт.',
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
