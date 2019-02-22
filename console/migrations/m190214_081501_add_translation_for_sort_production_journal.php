<?php

use yii\db\Migration;

/**
 * Class m190214_081501_add_translation_for_sort_production_journal
 */
class m190214_081501_add_translation_for_sort_production_journal extends Migration
{
    public $translations = [
        'production_journal.product_name'  => 'Названию продукции А-Я',
        'production_journal.-product_name' => 'Названию продукции Я-А',
        'production_journal.create_date'   => 'Дате создания продукции по возрастанию',
        'production_journal.-create_date'  => 'Дате создания продукции по убыванию',
        'production_journal.expiry_date'   => 'Сроку годности продукции по возрастанию',
        'production_journal.-expiry_date'  => 'Сроку годности продукции по убыванию',
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
