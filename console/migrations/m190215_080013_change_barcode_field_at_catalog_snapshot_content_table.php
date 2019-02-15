<?php

use yii\db\Migration;

/**
 * Class m190215_080013_change_barcode_field_at_catalog_snapshot_content_table
 */
class m190215_080013_change_barcode_field_at_catalog_snapshot_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%catalog_snapshot_content}}', 'barcode', $this->string(30));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%catalog_snapshot_content}}', 'barcode', $this->bigInteger(13));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190215_080013_change_barcode_field_at_catalog_snapshot_content_table cannot be reverted.\n";

        return false;
    }
    */
}
