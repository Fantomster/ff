<?php

use yii\db\Migration;

/**
 * Class m180723_115651_create_catalog_snapshot_and_catalog_snapshot_content_tables
 */
class m180723_115651_create_catalog_snapshot_and_catalog_snapshot_content_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%catalog_snapshot}}', [
            'id' => $this->primaryKey(),
            'cat_id' => $this->integer()->notNull(),
            'main_index' => $this->string()->notNull(),
            'currency_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null(),
                ], $tableOptions);

        $this->createTable('{{%catalog_snapshot_content}}', [
            'id' => $this->primaryKey(),
            'snapshot_id' => $this->integer()->notNull(),
            'article' => $this->string()->null(),
            'product' => $this->string()->null(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'market_place' => $this->integer()->notNull()->defaultValue(0),
            'deleted' => $this->integer()->notNull()->defaultValue(0),
            'price' => $this->decimal(10,2)->null()->defaultValue(0),
            'units' => $this->float()->null(),
            'category_id' => $this->integer()->null(),
            'note' => $this->string()->null(),
            'ed' => $this->string()->notNull()->defaultValue(''),
            'image' => $this->string()->null(),
            'brand' => $this->string()->null(),
            'region' => $this->string()->null(),
            'weight' => $this->string()->null(),
            'mp_show_price' => $this->integer()->notNull()->defaultValue(0),
            'barcode' => $this->bigInteger(13),
            'edi_supplier_article' => $this->string(30),
            'ssid' => $this->string(),
                ], $tableOptions);

        $this->addForeignKey('{{%fk_snapshot_content}}', '{{%catalog_snapshot_content}}', 'snapshot_id', '{{%catalog_snapshot}}', 'id');
        $this->addForeignKey('{{%fk_catalog_snapshot}}', '{{%catalog_snapshot}}', 'cat_id', '{{%catalog}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_snapshot_content}}', '{{%catalog_snapshot_content}}');
        $this->dropForeignKey('{{%fk_catalog_snapshot}}', '{{%catalog_snapshot}}');
        $this->dropTable('{{%catalog_snapshot}}');
        $this->dropTable('{{%catalog_snapshot_content}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180723_115651_create_catalog_snapshot_and_catalog_snapshot_content_tables cannot be reverted.\n";

        return false;
    }
    */
}
