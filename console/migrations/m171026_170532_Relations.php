<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170532_Relations extends Migration
{

    public function init()
    {
       $this->db = 'db_api';
       parent::init();
    }

    public function safeUp()
    {
        $this->addForeignKey('fk_organization_type_id',
            '{{%organization}}','type_id',
            '{{%organization_type}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_profile_user_id',
            '{{%profile}}','user_id',
            '{{%user}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_user_role_id',
            '{{%user}}','role_id',
            '{{%role}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_user_auth_user_id',
            '{{%user_auth}}','user_id',
            '{{%user}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_user_token_user_id',
            '{{%user_token}}','user_id',
            '{{%user}}','id',
            'CASCADE','CASCADE'
         );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_organization_type_id', '{{%organization}}');
        $this->dropForeignKey('fk_profile_user_id', '{{%profile}}');
        $this->dropForeignKey('fk_user_role_id', '{{%user}}');
        $this->dropForeignKey('fk_user_auth_user_id', '{{%user_auth}}');
        $this->dropForeignKey('fk_user_token_user_id', '{{%user_token}}');
    }
}
