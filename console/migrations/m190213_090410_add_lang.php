<?php

use yii\db\Migration;

/**
 * Class m190213_090410_add_lang
 */
class m190213_090410_add_lang extends Migration
{
    public $translations = [
        'organization_contact.type_check_error' => 'Не удалось определить тип контакта, поддежриваются email и номер телефона',
        'lazy_vendor.contact_exists'            => 'Такой контакт уже существует',
        'lazy_vendor.type_not_found'            => 'Тип контака введен не верно',
        'lazy_vendor.types_do_not_match'        => 'Конфликт в типе контактов',
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
