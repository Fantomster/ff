<?php

use yii\db\Migration;

/**
 * Class m190205_144626_drop_bullshit_fk
 */
class m190205_144626_drop_bullshit_fk extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropForeignKey('{{%organization_dictionary_org}}', '{{%organization_dictionary}}');
    }

    public function safeDown()
    {
        $this->addForeignKey('{{%organization_dictionary_org}}', '{{%organization_dictionary}}', 'org_id', '{{%organization_dictionary}}', 'id');
    }
}
