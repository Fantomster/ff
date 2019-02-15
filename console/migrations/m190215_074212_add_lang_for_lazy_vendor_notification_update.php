<?php

use yii\db\Migration;

/**
 * Class m190215_074212_add_lang_for_lazy_vendor_notification_update
 */
class m190215_074212_add_lang_for_lazy_vendor_notification_update extends Migration
{
    public $translations = [
        'lazy_vendor.wrong_contact_id'      => 'Один из идентификаторов принадлежит контактам другого поставщика.',
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
