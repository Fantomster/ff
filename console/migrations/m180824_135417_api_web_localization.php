<?php

use yii\db\Migration;

/**
 * Class m180824_135417_api_web_localization
 */
class m180824_135417_api_web_localization extends Migration
{
    public $translations_ru = [
        'catalog_temp_exists_duplicate' => 'Во временном каталоге обнаружены дубли',
        'catalog_temp_not_found' => 'Не найден временный каталог',
        'catalog_temp_content_not_found' => 'Временный каталог пуст',
        'base_catalog_not_found' => 'Главный каталог не найден'
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
