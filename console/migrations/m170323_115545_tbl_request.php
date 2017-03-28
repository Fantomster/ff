<?php

use yii\db\Migration;
use yii\db\Schema;

class m170323_115545_tbl_request extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%request}}', [
            'id' => Schema::TYPE_PK,
            'category' => Schema::TYPE_INTEGER . ' not null',
            'product' => Schema::TYPE_STRING . ' not null',
            'comment' => Schema::TYPE_STRING . ' null',
            'regular' => Schema::TYPE_STRING . ' null',
            'amount' => Schema::TYPE_STRING . ' not null',
            'rush_order' => Schema::TYPE_INTEGER . ' null',
            'payment_method' => Schema::TYPE_INTEGER . ' null',
            'deferment_payment' => Schema::TYPE_STRING . ' null',
            'responsible_supp_org_id' => Schema::TYPE_INTEGER . ' null',
            'count_views' => Schema::TYPE_INTEGER . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->createTable('{{%request_callback}}', [
            'id' => Schema::TYPE_PK,
            'request_id' => Schema::TYPE_INTEGER . ' not null',
            'supp_org_id' => Schema::TYPE_INTEGER . ' not null',
            'price' => Schema::TYPE_INTEGER . ' not null',
            'comment' => Schema::TYPE_STRING . ' null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->addForeignKey('{{%request_assoc}}', '{{%request_callback}}', 'request_id', '{{%request}}', 'id');
        
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%request_assoc}}', '{{%request_callback}}');
        $this->dropTable('{{%request}}');
        $this->dropTable('{{%request_callback}}');
    }
}
