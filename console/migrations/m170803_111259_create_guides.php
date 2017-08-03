<?php

use yii\db\Migration;

class m170803_111259_create_guides extends Migration {

    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%guide}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'deleted' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
                ], $tableOptions);

        $this->createTable('{{%guide_product}}', [
            'id' => $this->primaryKey(),
            'guide_id' => $this->integer()->notNull(),
            'cbg_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null(),
            'updated_at' => $this->timestamp()->null(),
                ], $tableOptions);

        $this->addForeignKey('{{%fk_guide_client}}', '{{%guide}}', 'client_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%fk_guide_product_guide}}', '{{%guide_product}}', 'guide_id', '{{%guide}}', 'id');
        $this->addForeignKey('{{%fk_guide_product_cbg}}', '{{%guide_product}}', 'cbg_id', '{{%catalog_base_goods}}', 'id');
    }

    public function safeDown() {
        $this->dropForeignKey('{{%fk_guide_client}}', '{{%guide}}');
        $this->dropForeignKey('{{%fk_guide_product_guide}}', '{{%guide_product}}');
        $this->dropForeignKey('{{%fk_guide_product_cbg}}', '{{%guide_product}}');
        $this->dropTable('{{%guide}}');
        $this->dropTable('{{%guide_product}}');
    }

}
