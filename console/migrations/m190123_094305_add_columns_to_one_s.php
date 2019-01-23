<?php

use yii\db\Migration;

/**
 * Class m190123_094305_add_columns_to_one_s
 */
class m190123_094305_add_columns_to_one_s extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%one_s_contragent}}', 'is_active', $this->boolean()->defaultValue(1));
        $this->addColumn('{{%one_s_good}}', 'is_active', $this->boolean()->defaultValue(1));
        $this->addColumn('{{%one_s_store}}', 'is_active', $this->boolean()->defaultValue(1));

        $this->addCommentOnColumn('{{%one_s_contragent}}', 'is_active', 'Показатель состояния активности в системе 1С');
        $this->addCommentOnColumn('{{%one_s_good}}', 'is_active', 'Показатель состояния активности в системе 1С');
        $this->addCommentOnColumn('{{%one_s_store}}', 'is_active', 'Показатель состояния активности в системе 1С');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190123_094305_add_columns_to_one_s cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190123_094305_add_columns_to_one_s cannot be reverted.\n";

        return false;
    }
    */
}
