<?php

use yii\db\Migration;

class m190219_115001_add_translations_errors extends Migration
{
    public $translations = [
        'common.edi.organization.not.found' => 'Такой организации в EDI не существует.',
        'common.organization.not.found'     => 'Такой организации не существует.',
        'common.order.not.found'            => 'Такого заказа не существует.',
        'common.user.not.found'             => 'Такого пользователя не существует.',
        'common.order.not.saving'           => 'Заказ сохранить не удалось.',
        'common.order.content.not.saving'   => 'Товарную позицию заказа сохранить не удалось.',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'error', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'error', $this->translations);
    }
}
