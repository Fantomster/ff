<?php

use yii\db\Migration;

/**
 * Class m180717_080611_add_new_const_to_merc_dicconst
 */
class m180717_080611_add_new_const_to_merc_dicconst extends Migration
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
        $this->insert('{{%merc_dicconst}}', [
            'denom' => 'vetis_password', 
            'def_value' => 'password',
            'comment' => 'Пароль пользователя Ветис',
            'type' => 3,
            'is_active' => 1,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%merc_dicconst}}', ['denom' => 'vetis_password']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180717_080611_add_new_const_to_merc_dicconst cannot be reverted.\n";

        return false;
    }
    */
}
