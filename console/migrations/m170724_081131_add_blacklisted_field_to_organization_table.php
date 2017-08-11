<?php

use yii\db\Migration;

class m170724_081131_add_blacklisted_field_to_organization_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'blacklisted', $this->integer()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'blacklisted');
    }
}
