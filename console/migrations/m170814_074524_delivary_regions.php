<?php

use yii\db\Migration;
use yii\db\Schema;

class m170814_074524_delivary_regions extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%delivery_regions}}', [
            'id' => Schema::TYPE_PK,
            'supplier_id' => Schema::TYPE_INTEGER . ' not null',
            'country' => Schema::TYPE_STRING . ' not null',
            'locality' => Schema::TYPE_STRING . ' null',
            'administrative_area_level_1' => Schema::TYPE_STRING . ' null',
            'exception' => Schema::TYPE_INTEGER . ' not null default 0',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
            
        ], $tableOptions);

        $this->addForeignKey('{{%supplier_assoc}}', '{{%delivery_regions}}', 'supplier_id', '{{%organization}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%supplier_assoc}}', '{{%delivery_regions}}');
        $this->dropTable('{{%delivery_regions}}');
    }
}
