<?php

use yii\db\Migration;
use yii\db\Schema;

class m170519_160435_create_tbl_franchisee_geo extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%franchisee_geo}}', [
            'id' => Schema::TYPE_PK,
            'franchisee_id' => Schema::TYPE_INTEGER . ' not null',
            'country' => Schema::TYPE_STRING . ' not null',
            'city' => Schema::TYPE_STRING . ' not null',
            'belongs_to' => Schema::TYPE_INTEGER . ' not null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);

        $this->addForeignKey('{{%franchisee_assoc}}', '{{%franchisee_geo}}', 'franchisee_id', '{{%franchisee}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%franchisee_assoc}}', '{{%franchisee_geo}}');
        $this->dropTable('{{%franchisee_geo}}');
    }
}
