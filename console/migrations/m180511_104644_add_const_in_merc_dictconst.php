<?php

use yii\db\Migration;

/**
 * Class m180511_104644_add_const_in_merc_dictconst
 */
class m180511_104644_add_const_in_merc_dictconst extends Migration
{

    public $tableName = '{{%merc_dicconst}}';

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
        $this->delete($this->tableName, ['denom' => 'org_name']);
        $this->insert($this->tableName, [
            'denom' => 'api_key',
            'def_value' => 'APIKey',
            'comment' => 'APIKey ВестисAPI',
            'type' => 2,
            'is_active' => 1
        ]);
        $this->insert($this->tableName, [
            'denom' => 'issuer_id ',
            'def_value' => 'IssuerID ',
            'comment' => 'Идентификатор хозяйствующего субъекта в реестре РСХН.',
            'type' => 2,
            'is_active' => 1
        ]);
        $this->insert($this->tableName, [
            'denom' => 'vetis_login',
            'def_value' => 'Vetis_login',
            'comment' => 'Логин пользователя Ветис',
            'type' => 2,
            'is_active' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180511_104644_add_const_in_merc_dictconst cannot be reverted.\n";

        return false;
    }

}
