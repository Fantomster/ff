<?php

use yii\db\Migration;

/**
 * Class m190124_075024_add_lang
 */
class m190124_075024_add_lang extends Migration
{

    public $translations = [
        'dictionary.product_type' => 'Типы продуктов',
        'dictionary.product_type_not_found' => 'Такой тип продуктов не найден',
        'organization.access_denied' => 'Доступ к организации запрещен.',
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
