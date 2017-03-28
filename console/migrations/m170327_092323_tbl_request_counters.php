<?php
use yii\db\Schema;
use yii\db\Migration;

class m170327_092323_tbl_request_counters extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%request_counters}}', [
            'id' => Schema::TYPE_PK,
            'request_id' => Schema::TYPE_INTEGER . ' not null',
            'user_id' => Schema::TYPE_INTEGER . ' not null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);
        $this->addForeignKey('{{%users_assoc}}', '{{%request_counters}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%request_assoc_}}', '{{%request_counters}}', 'request_id', '{{%request}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%users_assoc}}', '{{%request_counters}}');
        $this->dropForeignKey('{{%request_assoc_}}', '{{%request_counters}}');
        $this->dropTable('{{%request_counters}}');
    }
}
