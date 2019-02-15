<?php

use yii\db\Migration;

/**
 * Class m190214_070731_add_lang_for_lazy_vendor_notification_update
 */
class m190214_070731_add_lang_for_lazy_vendor_notification_update extends Migration
{
    public $translations = [
        'lazy_vendor.no_required_param'      => 'Нет одного из параметров.',
        'lazy_vendor.wrong_value'            => 'Один из параметров имеет недопустимое значение.',
        'lazy_vendor.not_your_notifications' => 'Вы пытаетесь изменить не свои уведомления.',
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
