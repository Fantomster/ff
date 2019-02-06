<?php

use yii\db\Migration;

/**
 * Class m190124_083513_add_translation_error_regionalization
 */
class m190124_083513_add_translation_error_regionalization extends Migration
{
    public $translations = [
        'Error getting data on regionalization' => 'Ошибка получения данных по регионализации',
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
