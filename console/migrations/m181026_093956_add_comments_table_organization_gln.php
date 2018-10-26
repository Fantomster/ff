<?php

use yii\db\Migration;

class m181026_093956_add_comments_table_organization_gln extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `organization_gln` comment "Таблица сведений об уникальных номерах GLN организаций";');
        $this->addCommentOnColumn('{{%organization_gln}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%organization_gln}}', 'org_id','Идентификатор организации');
        $this->addCommentOnColumn('{{%organization_gln}}', 'gln_number','Номер GLN');
        $this->addCommentOnColumn('{{%organization_gln}}', 'edi_provider_id','Идентификатор провайдeра EDI');
        $this->addCommentOnColumn('{{%organization_gln}}', 'gln_default_flag','Показатель предпочтительности для организации данного номера GLN (0 - не предпочтительный, 1 - предпочтительный)');
    }

    public function safeDown()
    {
        $this->execute('alter table `organization_gln` comment "";');
        $this->dropCommentFromColumn('{{%organization_gln}}', 'id');
        $this->dropCommentFromColumn('{{%organization_gln}}', 'org_id');
        $this->dropCommentFromColumn('{{%organization_gln}}', 'gln_number');
        $this->dropCommentFromColumn('{{%organization_gln}}', 'edi_provider_id');
        $this->dropCommentFromColumn('{{%organization_gln}}', 'gln_default_flag');
    }
}
