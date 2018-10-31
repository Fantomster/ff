<?php

use yii\db\Migration;

/**
 * Class m181031_120348_add_version_field_for_email_robo
 */
class m181031_120348_add_version_field_for_email_robo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%integration_setting_from_email}}', 'version', $this->tinyInteger()->defaultValue(1)->comment('Версия приложения MixCart'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%integration_setting_from_email}}', 'version');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181031_120348_add_version_field_for_email_robo cannot be reverted.\n";

        return false;
    }
    */
}
