<?php

use yii\db\Migration;

/**
 * Class m181022_073014_add_lang_web_api_dict
 */
class m181022_073014_add_lang_web_api_dict extends Migration
{
    public $translations_ru = [
        'dictionary.agent'    => 'Контрагенты',
        'dictionary.store'    => 'Склады',
        'dictionary.product'  => 'Номенклатура',
        'dictionary.unit'     => 'Единицы измерения',
        'dictionary.category' => 'Товарные группы'
    ];

    public $translations_ru_app = [
        'organization_dictionary.status.disabled'     => 'Синхронизация не проводилась',
        'organization_dictionary.status.active'       => 'Загружены',
        'organization_dictionary.status.error'        => 'Ошибка при загрузке',
        'organization_dictionary.status.send_request' => 'Запрос отправлен'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations_ru_app);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations_ru_app);
    }
}
