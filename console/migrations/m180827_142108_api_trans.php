<?php

use yii\db\Migration;

/**
 * Class m180827_142108_api_trans
 */
class m180827_142108_api_trans extends Migration
{
    public $translations_ru = [
        'catalog_not_found' => 'Каталог не найден',
        'this_is_not_your_catalog' => 'Это не ваш каталог',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
