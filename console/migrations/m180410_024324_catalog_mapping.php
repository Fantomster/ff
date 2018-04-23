<?php

use yii\db\Migration;

/**
 * Class m180410_024324_catalog_mapping
 */
class m180410_024324_catalog_mapping extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog}}', 'mapping', $this->string(255)->null()->defaultValue(null));
        $this->addColumn('{{%catalog}}', 'index_column', $this->integer()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog}}', 'mapping');
        $this->dropColumn('{{%catalog}}', 'index_column');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180410_024324_catalog_mapping cannot be reverted.\n";

        return false;
    }
    */
}
