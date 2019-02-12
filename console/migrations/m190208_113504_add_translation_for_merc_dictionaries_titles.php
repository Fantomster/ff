<?php

use yii\db\Migration;

/**
 * Class m190208_113504_add_translation_for_merc_dictionaries_titles
 */
class m190208_113504_add_translation_for_merc_dictionaries_titles extends Migration
{
    public $translations = [
        'dictionary.businessEntity'    => 'Хозяйствующие субъекты',
        'dictionary.russianEnterprise' => 'Отечественные предприятия',
        'dictionary.foreignEnterprise' => 'Импортные предприятия',
        'dictionary.productItem'       => 'Наименование продукции (4 уровень)',
        'dictionary.transport'         => 'Транспортные средства',
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
