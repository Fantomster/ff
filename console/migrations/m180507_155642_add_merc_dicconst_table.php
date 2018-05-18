<?php

use yii\db\Migration;

/**
 * Class m180507_155642_add_merc_dicconst_table
 */
class m180507_155642_add_merc_dicconst_table extends Migration
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
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->tableName}
            (
              id        INT AUTO_INCREMENT PRIMARY KEY,
              denom     VARCHAR(255) NULL,
              def_value VARCHAR(255) NULL,
              comment   VARCHAR(255) NULL,
              type      INT          NULL,
              is_active INT(2)       NULL
            );
        ";
        $this->execute($sql);

        $this->insert($this->tableName, [
            'denom' => 'auth_login',
            'def_value' => 'login',
            'comment' => 'Логин пользователя для подключения',
            'type' => 2,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'auth_password',
            'def_value' => 'password',
            'comment' => 'Пароль для подключения',
            'type' => 3,
            'is_active' => 1
        ]);

        $this->insert($this->tableName, [
            'denom' => 'org_name',
            'def_value' => 'Company name',
            'comment' => 'Название организации',
            'type' => 1,
            'is_active' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropTable($this->tableName);
    }

}
