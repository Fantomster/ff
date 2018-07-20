<?php

use yii\db\Migration;

/**
 * Class m180705_133223_add_translations_for_merc_vsd_mail
 */
class m180705_133223_add_translations_for_merc_vsd_mail extends Migration
{
    public $translations = [
        'common.mail.merc_vsd.subject' => 'Уведомление о непогашенных ВСД',
        'common.mail.merc_vsd.vsd_count' => 'Количество непогашенных ВСД',
        'common.mail.merc_vsd.process_button' => 'Перейти к гашению',
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
        echo "m180705_133223_add_translations_for_merc_vsd_mail cannot be reverted.\n";

        return false;
    }
    */
}
