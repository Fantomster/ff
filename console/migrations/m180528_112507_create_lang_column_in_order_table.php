<?php

use yii\db\Migration;

/**
 * Handles the creation of table `lang_column_in_order`.
 */
class m180528_112507_create_lang_column_in_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'lang', $this->string(5)->defaultValue('ru'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'lang');
    }
}
