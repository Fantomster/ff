<?php

use yii\db\Migration;

/**
 * Class m190218_134851_lazy_vendor_product_status_add_lang
 */
class m190218_134851_lazy_vendor_product_status_add_lang extends Migration
{
    public $translations = [
        'catalog.no_such_product' => 'Такого продукта нет в каталоге.',
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
