<?php

use yii\db\Migration;

/**
 * Class m181218_071706_drop_edi_organization_id_column
 */
class m181218_071706_drop_edi_organization_id_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%order}}', 'edi_organization_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181218_071706_drop_edi_organization_id_column cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181218_071706_drop_edi_organization_id_column cannot be reverted.\n";

        return false;
    }
    */
}
