<?php

use yii\db\Migration;

/**
 * Class m181018_071018_fill_edi_organization_table
 */
class m181018_071018_fill_edi_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $operations = $this->db->createCommand("SELECT organization_id, gln_code FROM edi_organization WHERE gln_code IS NOT NULL ")->queryAll();
        $this->batchInsert('{{%organization_gln}}', ['org_id', 'gln_number'], $operations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%organization_gln}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181018_071018_fill_edi_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
