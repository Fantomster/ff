<?php

use yii\db\Migration;

/**
 * Class m181108_082939_rebuild_roaming_map_table
 */
class m181108_082939_rebuild_roaming_map_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%organization_gln}}');
        $this->dropTable('{{%ecom_integration_config}}');
        $this->createTable('{{%edi_roaming_map}}', [
            'id' => $this->primaryKey(),
            'sender_edi_organization_id' => $this->integer(),
            'recipient_edi_organization_id' => $this->integer(),
            'created_by_id' => $this->integer()
        ]);
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'sender_edi_organization_id', 'Идентификатор связи ресторана в таблице edi_organization');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'recipient_edi_organization_id', 'Идентификатор связи поставщика в таблице edi_organization');
        $this->addCommentOnColumn('{{%edi_roaming_map}}', 'created_by_id', 'Идентификатор создателя записи');

        $this->addForeignKey('sender_edi_organization_idx', '{{%edi_roaming_map}}', 'sender_edi_organization_id', 'edi_organization', 'id');
        $this->addForeignKey('recipient_edi_organization_idx', '{{%edi_roaming_map}}', 'recipient_edi_organization_id', 'edi_organization', 'id');
        $this->addForeignKey('created_by_idx', '{{%edi_roaming_map}}', 'created_by_id', 'user', 'id');

        $this->addColumn('order', 'edi_organization_id', $this->integer());
        $this->addCommentOnColumn('order', 'edi_organization_id', 'Идентификатор связи ресторана в таблице edi_organization');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
