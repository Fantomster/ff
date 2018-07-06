<?php

use yii\db\Migration;

/**
 * Class m180706_071438_add_translations_for_merc_vsd_notifications
 */
class m180706_071438_add_translations_for_merc_vsd_notifications extends Migration
{
    public $translations = [
        'frontend.views.settings.vsd_notification' => 'Рассылки о непогашенных ВСД',
        'common.models.additional_email.vsd_short' => 'ВСД',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180706_071438_add_translations_for_merc_vsd_notifications cannot be reverted.\n";

        return false;
    }
    */
}
