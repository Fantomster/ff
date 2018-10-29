<?php

use yii\db\Migration;

/**
 * Class m181026_072251_add_vat_column_to_catalog_base_goods
 */
class m181026_072251_add_vat_column_to_catalog_base_goods extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%catalog_goods}}', 'vat', $this->integer()->null());
        $this->addCommentOnColumn('{{%catalog_goods}}', 'vat', 'Ставка НДС');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%catalog_goods}}', 'vat');
    }
}
