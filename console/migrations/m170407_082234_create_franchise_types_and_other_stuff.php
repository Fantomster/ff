<?php

use yii\db\Migration;

class m170407_082234_create_franchise_types_and_other_stuff extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%franchise_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'share' => $this->decimal(10,2)->notNull(),
            'price' => $this->decimal(10,2)->notNull(),
        ], $tableOptions);
        
        $this->addColumn('{{%relation_supp_rest}}', 'is_from_market', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%relation_supp_rest}}', 'deleted', $this->boolean()->notNull()->defaultValue(false));
    }

    public function safeDown()
    {
        $this->dropTable('{{%franchise_type}}');
        $this->dropColumn('{{%relation_supp_rest}}', 'is_from_market');
        $this->dropColumn('{{%relation_supp_rest}}', 'deleted');
    }
}
