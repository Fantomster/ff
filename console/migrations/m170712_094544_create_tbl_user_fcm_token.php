<?php

use yii\db\Migration;
use yii\db\Schema;

class m170712_094544_create_tbl_user_fcm_token extends Migration
{
       public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user_fcm_token}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' not null',
            'token' => Schema::TYPE_STRING . ' not null',
            'device_id' => Schema::TYPE_STRING . ' not null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' null',
        ], $tableOptions);

        $this->addForeignKey('{{%user}}', '{{%user_fcm_token}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%user}}', '{{%user_fcm_token}}');
        $this->dropTable('{{%user_fcm_token}}');
    }
}
