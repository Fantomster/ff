<?php

use yii\db\Migration;

/**
 * Class m190206_084529_add_translate_for_vetis_exceptions
 */
class m190206_084529_add_translate_for_vetis_exceptions extends Migration
{
    public $translations = [
        'vetis.setting_issuer_id_not_defined' => 'Не установлена настройка для Меркурия - GUID субъекта в реестре РСХН',
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
