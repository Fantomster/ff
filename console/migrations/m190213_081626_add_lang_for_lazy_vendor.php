<?php

use yii\db\Migration;

/**
 * Class m190213_081626_add_lang_for_lazy_vendor
 */
class m190213_081626_add_lang_for_lazy_vendor extends Migration
{
    public $translations = [
        'lazy_vendor.rest_or_common_supplier' => 'Этот email принадлежит обычному поставщику или ресторану.'
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
