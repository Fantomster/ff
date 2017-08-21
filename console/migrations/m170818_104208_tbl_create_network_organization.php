<?php

use yii\db\Migration;

class m170818_104208_tbl_create_network_organization extends Migration
{
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%network_organization}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
                ], $tableOptions);

        $this->addForeignKey('{{%relation_organization_id}}', '{{%network_organization}}', 'organization_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%relation_parent_id}}', '{{%network_organization}}', 'parent_id', '{{%organization}}', 'id');
    }

    public function safeDown() {
        $this->dropForeignKey('{{%relation_organization_id}}', '{{%network_organization}}');
        $this->dropForeignKey('{{%relation_parent_id}}', '{{%network_organization}}');
        $this->dropTable('{{%network_organization}}');
    }  
}
