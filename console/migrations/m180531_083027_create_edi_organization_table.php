<?php

use yii\db\Migration;

/**
 * Handles the creation of table `edi_organization`.
 */
class m180531_083027_create_edi_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organization', 'gln_code');
        $this->createTable('edi_organization', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(),
            'gln_code' => $this->bigInteger(17),
            'login' => $this->string(255),
            'pass' => $this->string(255)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('organization', 'gln_code', $this->bigInteger(17));
        $this->dropTable('edi_organization');
    }
}
