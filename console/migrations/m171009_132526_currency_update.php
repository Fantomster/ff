<?php

use yii\db\Migration;
use yii\db\Schema;

class m171009_132526_currency_update extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%currency}}', [
            'id' => Schema::TYPE_PK,
            'text' => Schema::TYPE_STRING . ' not null',
            'symbol' => Schema::TYPE_STRING . ' not null',
        ], $tableOptions);
        
        $this->batchInsert('{{%currency}}', ['text','symbol'], [
            ['руб', '₽'],
        ]);
        
        $this->addColumn('{{%catalog}}', 'currency_id', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('{{%order}}', 'currency_id', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('{{%guide_product}}', 'currency_id', $this->integer()->notNull()->defaultValue(1));
        $this->addForeignKey('{{%catalog_currency}}', '{{%catalog}}', 'currency_id', '{{%currency}}', 'id');
        $this->addForeignKey('{{%order_currency}}', '{{%order}}', 'currency_id', '{{%currency}}', 'id');
        $this->addForeignKey('{{%guide_currency}}', '{{%guide_product}}', 'currency_id', '{{%currency}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%catalog_currency}}', '{{%catalog}}');
        $this->dropForeignKey('{{%order_currency}}', '{{%order}}');
        $this->dropForeignKey('{{%guide_currency}}', '{{%guide_product}}');
        $this->dropColumn('{{%guide_product}}', 'currency_id');
        $this->dropColumn('{{%catalog}}', 'currency_id');
        $this->dropColumn('{{%order}}', 'currency_id');
        $this->dropTable('{{%currency}}');
    }
}
