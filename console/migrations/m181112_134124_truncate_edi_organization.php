<?php

use yii\db\Migration;

/**
 * Class m181112_134124_truncate_edi_organization
 */
class m181112_134124_truncate_edi_organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->dropForeignKey('sender_edi_organization_idx', 'edi_roaming_map');
        //$this->delete('{{%edi_organization}}', ['provider_id' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181112_134124_truncate_edi_organization cannot be reverted.\n";

        return false;
    }
    */
}
