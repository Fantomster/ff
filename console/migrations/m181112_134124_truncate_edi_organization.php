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
        $this->truncateTable('{{%edi_organization}}');
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
