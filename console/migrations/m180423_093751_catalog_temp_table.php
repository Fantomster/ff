<?php

use yii\db\Migration;

/**
 * Class m180423_093751_catalog_temp_table
 */
class m180423_093751_catalog_temp_table extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%catalog_temp}}', [
            'id' => $this->primaryKey(),
            'cat_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'excel_file' => $this->string()->notNull(),
            'mapping' => $this->string()->null(),
            'index_column' => $this->string()->null(),
            'created_at' => $this->timestamp()->null(),
                ], $tableOptions);

        $this->createTable('{{%catalog_temp_content}}', [
            'id' => $this->primaryKey(),
            'temp_id' => $this->integer()->notNull(),
            'article' => $this->string()->null(),
            'product' => $this->string()->null(),
            'price' => $this->decimal(10, 2)->null()->defaultValue(0),
            'units' => $this->float()->null(),
            'note' => $this->string()->null(),
            'ed' => $this->string()->notNull()->defaultValue(''),
                ], $tableOptions);

        $this->addForeignKey('{{%fk_temp_content}}', '{{%catalog_temp_content}}', 'temp_id', '{{%catalog_temp}}', 'id');
        $this->addForeignKey('{{%fk_catalog_temp}}', '{{%catalog_temp}}', 'cat_id', '{{%catalog}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropForeignKey('{{%fk_temp_content}}', '{{%catalog_temp_content}}');
        $this->dropForeignKey('{{%fk_catalog_temp}}', '{{%catalog_temp}}');
        $this->dropTable('{{%catalog_temp}}');
        $this->dropTable('{{%catalog_temp_content}}');
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m180423_093751_catalog_temp_table cannot be reverted.\n";

      return false;
      }
     */
}
