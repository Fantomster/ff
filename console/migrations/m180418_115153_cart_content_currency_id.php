<?php

use yii\db\Migration;

/**
 * Class m180418_115153_cart_content_currency_id
 */
class m180418_115153_cart_content_currency_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%cart_content}}', 'currency_id', $this->integer());
        $this->addForeignKey('{{%cart_content_relation_currency_id}}', '{{%cart_content}}', 'currency_id', '{{%currency}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%cart_content_relation_currency_id}}', '{{%cart_content}}');
        $this->dropColumn('{{%cart_content}}', 'currency_id');
    }
}
