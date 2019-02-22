<?php

use yii\db\Migration;

/**
 * Class m190218_121234_lazy_vendor_price_del_cat_add_lang
 */
class m190218_121234_lazy_vendor_price_del_cat_add_lang extends Migration
{
    public $translations = [
        'catalog.wrong_value' => 'Неправильное значение параметра.',
        'catalog.not_lazy_vendor' => 'Это не ленивый поставщик.',
        'catalog.not_exist' => 'У вас нет каталога у данного поставщика.',
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
