<?php

use yii\db\Migration;

/**
 * Class m190205_124828_add_translation_for_vetis
 */
class m190205_124828_add_translation_for_vetis extends Migration
{
    public $translations = [
        'vetis.setting_enterprise_guid_not_defined' => 'Не установлена настройка для Меркурия - GUID предприятия',
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
